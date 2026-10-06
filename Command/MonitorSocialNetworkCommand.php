<?php

declare(strict_types=1);

namespace MauticPlugin\MauticSocialBundle\Command;

use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticSocialBundle\Helper\BlueskyApiHelper;
use MauticPlugin\MauticSocialBundle\Helper\RedditApiHelper;
use MauticPlugin\MauticSocialBundle\Helper\XApiV2Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mautic:social:monitor', description: 'Run social network monitoring (x|mastodon|bluesky|reddit|youtube|yelp)')]
final class MonitorSocialNetworkCommand extends Command
{
    public function __construct(
        private readonly IntegrationHelper $integrationHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('network', null, InputOption::VALUE_REQUIRED, 'Network: x|mastodon|bluesky|reddit|youtube|yelp')
            ->addOption('query', null, InputOption::VALUE_REQUIRED, 'Search query / hashtag / subreddit')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Max results', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $network = strtolower((string) $input->getOption('network'));
        $query = (string) $input->getOption('query');
        $limit = (int) $input->getOption('limit');

        if ('' === $network || '' === $query) {
            $output->writeln('<error>--network and --query are required</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Monitoring %s for "%s" (limit %d)</info>', $network, $query, $limit));

        $count = match ($network) {
            'x', 'twitter' => $this->monitorX($query, $limit, $output),
            'mastodon' => $this->monitorMastodon($query, $limit, $output),
            'bluesky' => $this->monitorBluesky($query, $limit, $output),
            'reddit' => $this->monitorReddit($query, $limit, $output),
            'youtube' => $this->monitorYouTube($query, $limit, $output),
            'yelp' => $this->monitorYelp($query, $limit, $output),
            default => -1,
        };

        if (-1 === $count) {
            $output->writeln('<error>Unknown network. Use x|mastodon|bluesky|reddit|youtube|yelp</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>Fetched %d item(s)</info>', $count));
        return Command::SUCCESS;
    }

    private function monitorX(string $query, int $limit, OutputInterface $output): int
    {
        $integration = $this->integrationHelper->getIntegrationObject('Twitter');
        if (!$integration || !method_exists($integration, 'searchRecent')) {
            $output->writeln('<error>X/Twitter integration not available</error>');
            return 0;
        }
        $q = str_starts_with($query, '#') ? XApiV2Client::buildSearchQueryForHashtag($query) : $query;
        $response = $integration->searchRecent($q, ['max_results' => min(100, max(10, $limit))]);
        $data = $response['data'] ?? [];
        foreach ($data as $tweet) {
            $output->writeln(sprintf(' - [%s] %s', $tweet['id'] ?? '?', mb_substr($tweet['text'] ?? '', 0, 80)));
        }
        return count($data);
    }

    private function monitorMastodon(string $query, int $limit, OutputInterface $output): int
    {
        $integration = $this->integrationHelper->getIntegrationObject('Mastodon');
        if (!$integration) {
            $output->writeln('<error>Mastodon integration not available</error>');
            return 0;
        }
        $tag = ltrim($query, '#');
        $response = $integration->makeRequest(
            $integration->getApiUrl('timelines/tag/'.rawurlencode($tag)),
            ['limit' => $limit],
            'GET'
        );
        if (!is_array($response)) {
            return 0;
        }
        foreach ($response as $status) {
            if (!is_array($status)) {
                continue;
            }
            $output->writeln(sprintf(
                ' - [%s] @%s: %s',
                $status['id'] ?? '?',
                $status['account']['acct'] ?? '?',
                mb_substr(strip_tags($status['content'] ?? ''), 0, 80)
            ));
        }
        return count($response);
    }

    private function monitorBluesky(string $query, int $limit, OutputInterface $output): int
    {
        $integration = $this->integrationHelper->getIntegrationObject('Bluesky');
        if (!$integration) {
            $output->writeln('<error>Bluesky integration not available</error>');
            return 0;
        }
        $url = BlueskyApiHelper::xrpcUrl(BlueskyApiHelper::PUBLIC_API, 'app.bsky.feed.searchPosts');
        $response = $integration->makeRequest($url, ['q' => $query, 'limit' => $limit], 'GET');
        $posts = is_array($response) ? ($response['posts'] ?? []) : [];
        foreach ($posts as $post) {
            $output->writeln(sprintf(
                ' - @%s: %s',
                $post['author']['handle'] ?? '?',
                mb_substr($post['record']['text'] ?? '', 0, 80)
            ));
        }
        return count($posts);
    }

    private function monitorReddit(string $query, int $limit, OutputInterface $output): int
    {
        $integration = $this->integrationHelper->getIntegrationObject('Reddit');
        if (!$integration) {
            $output->writeln('<error>Reddit integration not available</error>');
            return 0;
        }
        $sr = RedditApiHelper::cleanSubreddit($query);
        $response = $integration->makeRequest(
            RedditApiHelper::apiUrl('r/'.rawurlencode($sr).'/new'),
            ['limit' => $limit],
            'GET'
        );
        $children = is_array($response) ? ($response['data']['children'] ?? []) : [];
        foreach ($children as $child) {
            $output->writeln(sprintf(' - %s', mb_substr($child['data']['title'] ?? '', 0, 80)));
        }
        return count($children);
    }

    private function monitorYouTube(string $query, int $limit, OutputInterface $output): int
    {
        $integration = $this->integrationHelper->getIntegrationObject('YouTube');
        if (!$integration || !method_exists($integration, 'search')) {
            $output->writeln('<error>YouTube integration not available</error>');
            return 0;
        }
        $response = $integration->search($query, $limit);
        $items = is_array($response) ? ($response['items'] ?? []) : [];
        foreach ($items as $item) {
            $title = $item['snippet']['title'] ?? '';
            $vid = $item['id']['videoId'] ?? '';
            $output->writeln(sprintf(' - [%s] %s', $vid, mb_substr($title, 0, 80)));
        }
        return count($items);
    }

    private function monitorYelp(string $query, int $limit, OutputInterface $output): int
    {
        $integration = $this->integrationHelper->getIntegrationObject('Yelp');
        if (!$integration || !method_exists($integration, 'searchBusinesses')) {
            $output->writeln('<error>Yelp integration not available</error>');
            return 0;
        }
        $parts = array_map('trim', explode('|', $query, 2));
        $term = $parts[0];
        $location = $parts[1] ?? 'United States';
        $response = $integration->searchBusinesses($term, $location, $limit);
        $businesses = is_array($response) ? ($response['businesses'] ?? []) : [];
        foreach ($businesses as $b) {
            $output->writeln(sprintf(
                ' - %s (%.1f) %s',
                $b['name'] ?? '?',
                (float) ($b['rating'] ?? 0),
                $b['location']['city'] ?? ''
            ));
        }
        return count($businesses);
    }
}

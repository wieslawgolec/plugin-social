<?php
/**
 * Writes 128x128 RGBA PNG icons from Assets/img/icons_base64.json.
 * Does not overwrite upstream icons: facebook, foursquare, instagram, linkedin, twitter.
 *
 *   php scripts/decode_icons.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$imgDir = $root.'/Assets/img';
$jsonPath = $imgDir.'/icons_base64.json';
$protected = ['facebook.png', 'foursquare.png', 'instagram.png', 'linkedin.png', 'twitter.png'];

if (!is_file($jsonPath)) {
    fwrite(STDERR, "Missing $jsonPath — pull latest main.\n");
    exit(1);
}

$icons = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);

foreach ($icons as $name => $b64) {
    $file = $name.'.png';
    if (in_array($file, $protected, true)) {
        echo "skip protected $file\n";
        continue;
    }
    $bin = base64_decode($b64, true);
    if (false === $bin) {
        fwrite(STDERR, "invalid base64 for $name\n");
        continue;
    }
    file_put_contents($imgDir.'/'.$file, $bin);
    echo "wrote $file (".strlen($bin)." bytes)\n";
}
echo "done\n";

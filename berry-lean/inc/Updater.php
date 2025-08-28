<?php
namespace BerryLean;

class Updater {
  public static function init(){
    $puc = BERRY_LEAN_DIR.'plugin-update-checker/plugin-update-checker.php';
    if (!file_exists($puc)) return;
    require_once $puc;

    if (!class_exists('\YahnisElsts\PluginUpdateChecker\v5\PucFactory')) return;
    $factory = '\YahnisElsts\PluginUpdateChecker\v5\PucFactory';

    $checker = $factory::buildUpdateChecker(
      'https://github.com/newcode4/berry-lean-wp',
      BERRY_LEAN_FILE,
      BERRY_LEAN_SLUG
    );
    $checker->setBranch('main');
    if (method_exists($checker->getVcsApi(), 'enableReleaseAssets')) {
      $checker->getVcsApi()->enableReleaseAssets();
    }
  }
}

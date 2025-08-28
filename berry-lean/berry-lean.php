<?php
/**
 * Plugin Name: 베리워크 린 유틸 (Berry Lean Utilities)
 * Description: 전역 CSS 변수(:root) + SEO 메타 + 탭 UI
 * Version: 1.3.1
 * Author: Berrywalk
 * Requires PHP: 7.4
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Text Domain: berry-lean
 */

if (!defined('ABSPATH')) exit;

define('BERRY_LEAN_FILE', __FILE__);
define('BERRY_LEAN_DIR',  plugin_dir_path(__FILE__));
define('BERRY_LEAN_URL',  plugin_dir_url(__FILE__));
define('BERRY_LEAN_SLUG', 'berry-lean');
define('BERRY_LEAN_OPT',  'berry_lean');
define('BERRY_LEAN_STYLE','berry-lean-style');
define('BERRY_LEAN_RT',   'berry-lean-runtime');

// ── 모듈 로드
require_once BERRY_LEAN_DIR.'inc/Updater.php';
require_once BERRY_LEAN_DIR.'inc/Snippets.php';
require_once BERRY_LEAN_DIR.'inc/Front.php';
require_once BERRY_LEAN_DIR.'inc/Admin.php';
require_once BERRY_LEAN_DIR.'inc/SEO.php';
require_once BERRY_LEAN_DIR.'inc/PageTags.php';

// ── 부트스트랩
add_action('plugins_loaded', function () {
  \BerryLean\Updater::init();
  \BerryLean\Front::init();
  \BerryLean\Admin::init();
  \BerryLean\SEO::init();
  \BerryLean\PageTags::init();
});

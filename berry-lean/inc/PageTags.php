<?php
namespace BerryLean;

class PageTags {
  public static function init(){
    add_action('init', [__CLASS__, 'maybe_enable'], 5);
    add_action('restrict_manage_posts', [__CLASS__, 'dropdown']);
    add_filter('parse_query', [__CLASS__, 'filter_query']);
  }

  public static function maybe_enable(){
    $sel = (array) get_option('berry_snippets',[]);
    if (!in_array('pageTags',$sel,true)) return;
    register_taxonomy_for_object_type('post_tag','page');
  }

  public static function dropdown($post_type){
    $sel = (array) get_option('berry_snippets',[]);
    if (!in_array('pageTags',$sel,true) || $post_type!=='page') return;

    $terms = get_terms(['taxonomy'=>'post_tag','hide_empty'=>false]);
    if (empty($terms) || is_wp_error($terms)) return;

    $selected = isset($_GET['post_tag']) ? sanitize_text_field($_GET['post_tag']) : '';
    echo "<select name='post_tag'><option value=''>태그별</option>";
    foreach($terms as $t){
      printf("<option value='%s'%s>%s</option>", esc_attr($t->slug), selected($selected,$t->slug,false), esc_html($t->name));
    }
    echo '</select>';
  }

  public static function filter_query($query){
    if (!is_admin()) return;
    global $pagenow;
    if ($pagenow!=='edit.php' || empty($_GET['post_type']) || $_GET['post_type']!=='page' || empty($_GET['post_tag'])) return;

    $query->query_vars['tax_query'] = [[
      'taxonomy'=>'post_tag','field'=>'slug','terms'=>sanitize_text_field($_GET['post_tag']),
    ]];
  }
}

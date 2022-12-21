<?php
/**
 * Plugin Name: Custom Post Count At A Glance 
 * Description: Display the count of custom posts in the WordPress dashboard in the 'At a glance' widget, like posts, pages and comment count.
 * Version: 1.0
 * Author: Prince Kumar
 * Author URI: http://onemoreprince.com
 * License: GPL2
 */

function cpt_glance_options_page() {
    add_options_page(
      'Custom Post Type Count Glance Options', // Page title
      'CPT Glance', // Menu title
      'manage_options', // Capability
      'cpt-glance', // Menu slug
      'cpt_glance_options_page_html' // Callback function
    );
  }
  add_action( 'admin_menu', 'cpt_glance_options_page' );
  function cpt_glance_options_page_html() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }
  
    // Check if the form was submitted
    if ( isset( $_POST['cpt_glance_options_nonce'] ) && wp_verify_nonce( $_POST['cpt_glance_options_nonce'], 'cpt_glance_options' ) ) {
      // Sanitize user input
      $selected_post_types = array_map( 'sanitize_text_field', $_POST['selected_post_types'] );
      // Update the option value
      update_option( 'cpt_glance_selected_post_types', $selected_post_types );
    }
  
    // Get the current value of the option
    $selected_post_types = get_option( 'cpt_glance_selected_post_types', array() );
    ?>
    <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <form method="post">
        <?php wp_nonce_field( 'cpt_glance_options', 'cpt_glance_options_nonce' ); ?>
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row"><label for="selected_post_types">Select Post Types</label></th>
              <td>
                <?php
                // Get all custom post types
                $post_types = get_post_types( array( '_builtin' => false ), 'objects' );
                foreach ( $post_types as $post_type ) {
                  ?>
                  <label>
                    <input type="checkbox" name="selected_post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $selected_post_types ) ); ?>>
                   
                    <?php echo esc_html( $post_type->labels->name ); ?>
                  </label><br>
                  <?php
                }
                ?>
              </td>
            </tr>
          </tbody>
        </table>
        <p class="submit">
          <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
        </p>
      </form>
    </div>
    <?php
  }
  function cpt_glance_items( $items = array() ) {
    // Get the selected custom post types
    $selected_post_types = get_option( 'cpt_glance_selected_post_types', array() );
    foreach ( $selected_post_types as $post_type ) {
      $num_posts = wp_count_posts( $post_type );
      if ( $num_posts ) {
        $published = intval( $num_posts->publish );
        $post_type_object = get_post_type_object( $post_type );
        $text = _n( '%s ' . $post_type_object->labels->singular_name, '%s ' . $post_type_object->labels->name, $published, 'text_domain' );
        $text = sprintf( $text, number_format_i18n( $published ) );
        if ( current_user_can( $post_type_object->cap->edit_posts ) ) {
          $items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', $post_type, $text ) . "\n";
        } else {
          $items[] = sprintf( '<span class="%1$s-count">%2$s</span>', $post_type, $text ) . "\n";
        }
      }
    }
    return $items;
  }
  add_filter( 'dashboard_glance_items', 'cpt_glance_items' );
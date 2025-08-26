<?php
/**
 * Handles the meta box and custom field for the sticky post option.
 *
 * @package HomepageStickySlider
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Adds the meta box to the post editor screen.
 *
 * This function is hooked into 'add_meta_boxes'.
 */
function hss_add_meta_box() {
    add_meta_box(
        'hss_sticky_post_meta_box', // Unique ID for the meta box.
        'Homepage Slider Option',   // Title of the meta box.
        'hss_meta_box_callback',    // Callback function to display the content.
        'post',                     // The post type where it should appear.
        'side',                     // The context (where on the page). 'side', 'normal', 'advanced'.
        'high'                      // The priority within the context.
    );
}
add_action( 'add_meta_boxes', 'hss_add_meta_box' );

/**
 * Renders the HTML content for the meta box.
 *
 * @param WP_Post $post The post object.
 */
function hss_meta_box_callback( $post ) {
    // Add a nonce field for security.
    wp_nonce_field( 'hss_save_meta_box_data', 'hss_meta_box_nonce' );

    // Get the saved meta value.
    $value = get_post_meta( $post->ID, '_is_sticky_post', true );

    // Display the dropdown.
    echo '<label for="hss_sticky_post_field">Feature this post?</label>';
    echo '<select name="hss_sticky_post_field" id="hss_sticky_post_field" class="widefat">';
    echo '<option value="no" ' . selected( $value, 'no', false ) . '>No</option>';
    echo '<option value="yes" ' . selected( $value, 'yes', false ) . '>Yes</option>';
    echo '</select>';
}

/**
 * Adds a custom column to the post list table in the admin area.
 *
 * @param array $columns The existing columns.
 * @return array The modified columns array.
 */
function hss_add_sticky_admin_column( $columns ) {
    // Add our column after the 'title' column for better placement.
    $new_columns = array();
    foreach ( $columns as $key => $title ) {
        $new_columns[$key] = $title;
        if ( $key === 'title' ) {
            $new_columns['hss_sticky_post'] = 'Homepage Slider';
        }
    }
    return $new_columns;
}
add_filter( 'manage_post_posts_columns', 'hss_add_sticky_admin_column' );

/**
 * Displays the content for our custom column in the post list table.
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id     The ID of the current post.
 */
function hss_display_sticky_column_content( $column_name, $post_id ) {
    if ( 'hss_sticky_post' === $column_name ) {
        $is_sticky = get_post_meta( $post_id, '_is_sticky_post', true );

        // Display the status for the user. The hidden input is no longer needed.
        if ( 'yes' === $is_sticky ) {
            echo '✅ Yes';
        } else {
            echo '—'; // Use an em dash for a clean look.
        }
    }
}
add_action( 'manage_post_posts_custom_column', 'hss_display_sticky_column_content', 10, 2 );

/**
 * Adds the custom field to the Quick Edit screen.
 *
 * @param string $column_name The name of the column being displayed.
 */
function hss_add_quick_edit_field( $column_name ) {
    if ( 'hss_sticky_post' === $column_name ) {
        // Add a nonce for security.
        wp_nonce_field( 'hss_save_quick_edit_data', 'hss_quick_edit_nonce' );
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label class="alignleft">
                    <span class="title">Feature on slider?</span>
                    <select name="hss_sticky_post_field">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select>
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action( 'quick_edit_custom_box', 'hss_add_quick_edit_field', 10, 1 );

/**
 * Saves the meta data from both the post editor and the Quick Edit screen.
 *
 * @param int $post_id The ID of the post being saved.
 */
function hss_save_meta_data( $post_id ) {
    // Check if this is an autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Check for nonce from either full editor or quick editor.
    $nonce_action = '';
    $nonce_name = '';

    if ( isset( $_POST['hss_meta_box_nonce'] ) ) {
        $nonce_action = 'hss_save_meta_box_data';
        $nonce_name = 'hss_meta_box_nonce';
    } elseif ( isset( $_POST['hss_quick_edit_nonce'] ) ) {
        $nonce_action = 'hss_save_quick_edit_data';
        $nonce_name = 'hss_quick_edit_nonce';
    } else {
        // No nonce found, so we bail.
        return;
    }

    // Verify the nonce.
    if ( ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
        return;
    }

    // Now, save the data. The field name is the same in both forms.
    if ( isset( $_POST['hss_sticky_post_field'] ) && 'yes' === $_POST['hss_sticky_post_field'] ) {
        update_post_meta( $post_id, '_is_sticky_post', 'yes' );
    } else {
        delete_post_meta( $post_id, '_is_sticky_post' );
    }
}
add_action( 'save_post', 'hss_save_meta_data' );


/**
 * Enqueues JavaScript to handle populating the Quick Edit field.
 */
function hss_enqueue_quick_edit_script( $hook ) {
    // Only run on the edit.php screen for posts.
    if ( 'edit.php' !== $hook || 'post' !== get_post_type() ) {
        return;
    }

    // Get all posts currently displayed on the screen.
    global $wp_query;
    $posts = $wp_query->posts;
    $sticky_data = array();

    // Create a simple array of [post_id => 'yes'/'no'].
    foreach ( $posts as $post ) {
        $is_sticky = get_post_meta( $post->ID, '_is_sticky_post', true );
        $sticky_data[ $post->ID ] = ( 'yes' === $is_sticky ) ? 'yes' : 'no';
    }

    // Use wp_add_inline_script to pass this data to the JavaScript environment.
    // We create a JS variable `hss_sticky_data` that holds our object.
    wp_add_inline_script( 'jquery-core', 'var hss_sticky_data = ' . wp_json_encode( $sticky_data ) . ';' );

    // Now, print the script that uses this data.
    add_action( 'admin_footer', function() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Use event delegation to listen for clicks on Quick Edit buttons.
                $('#the-list').on('click', '.editinline', function() {
                    // Get the post ID.
                    var post_id = inlineEditPost.getId(this);

                    // Get the sticky value from our pre-loaded JavaScript object.
                    var sticky_value = hss_sticky_data[post_id] || 'no';

                    // Use a short timeout to let the Quick Edit form be built.
                    setTimeout(function() {
                        // Find the Quick Edit row and set the dropdown value.
                        var edit_row = $('#edit-' + post_id);
                        $('select[name="hss_sticky_post_field"]', edit_row).val(sticky_value);
                    }, 50);
                });
            });
        </script>
        <?php
    });
}
add_action( 'admin_enqueue_scripts', 'hss_enqueue_quick_edit_script' );
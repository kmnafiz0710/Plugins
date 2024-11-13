<?php
/*
Plugin Name: Auto Category to Menu
Description: Automatically adds new categories (taxonomy terms) to a specified menu when they are created.
Version: 1.0
Author: Khaled Masud
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Add a checkbox in menu settings to enable auto-adding categories
add_action('wp_nav_menu_item_custom_fields', 'add_auto_add_category_checkbox', 10, 4);
function add_auto_add_category_checkbox($item_id, $item, $depth, $args) {
    ?>
    <p class="description description-wide">
        <label for="edit-menu-item-auto-add-category-<?php echo $item_id; ?>">
            <input type="checkbox" id="edit-menu-item-auto-add-category-<?php echo $item_id; ?>" 
                   name="menu-item-auto-add-category[<?php echo $item_id; ?>]" 
                   value="1" <?php checked(get_post_meta($item_id, '_auto_add_category', true), 1); ?> />
            Automatically add new categories to this menu
        </label>
    </p>
    <?php
}

// Save the checkbox state
add_action('wp_update_nav_menu_item', 'save_auto_add_category_checkbox', 10, 3);
function save_auto_add_category_checkbox($menu_id, $menu_item_db_id, $args) {
    if (isset($_POST['menu-item-auto-add-category'][$menu_item_db_id])) {
        update_post_meta($menu_item_db_id, '_auto_add_category', 1);
    } else {
        delete_post_meta($menu_item_db_id, '_auto_add_category');
    }
}


// Hook into category creation to add it to the menu if the option is enabled
add_action('created_category', 'auto_add_category_to_menu', 10, 2);
function auto_add_category_to_menu($term_id, $taxonomy) {
    if ($taxonomy !== 'category') return; // Adjust if needed for other taxonomies
    
    // Loop through all menus to check for the checkbox state
    $menus = wp_get_nav_menus();
    foreach ($menus as $menu) {
        $menu_items = wp_get_nav_menu_items($menu->term_id);

        // Check if the auto-add option is enabled for any item in the menu
        foreach ($menu_items as $item) {
            if (get_post_meta($item->ID, '_auto_add_category', true)) {
                // Add the new category to this menu
                wp_update_nav_menu_item($menu->term_id, 0, array(
                    'menu-item-title' => get_cat_name($term_id),
                    'menu-item-object-id' => $term_id,
                    'menu-item-object' => 'category',
                    'menu-item-type' => 'taxonomy',
                    'menu-item-status' => 'publish',
                ));
                break; // Stop after adding to the first menu with the checkbox enabled
            }
        }
    }
}


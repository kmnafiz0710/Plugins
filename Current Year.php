// Dynamic year
function dynamic_year_shortcode() {
    $current_year = date('Y');
    return $current_year;
}

// Dynamic year shortcode
add_shortcode('current_year', 'dynamic_year_shortcode');

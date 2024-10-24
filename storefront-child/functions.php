<?php
function storefront_child_enqueue_styles()
{
    wp_enqueue_style('storefront-parent-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'storefront_child_enqueue_styles');

function display_related_products_grid()
{
    global $product;

    $related_ids = $product->get_related();

    if (empty($related_ids)) return;

    $categories = wp_get_post_terms($product->get_id(), 'product_cat');
    $category_names = [];

    if (!empty($categories)) {
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }
    }

    $related_products = new WP_Query(array(
        'post_type' => 'product',
        'post__in' => $related_ids,
        'posts_per_page' => 6,
        'orderby' => 'rand'
    ));

    if ($related_products->have_posts()) {
        echo '<div class="related-products-grid">';
        echo '<h2>' . __('Otros Productos de la categoría: ', 'woocommerce') . implode(', ', $category_names) . '</h2>';
        echo '<div class="grid-container">';

        while ($related_products->have_posts()) {
            $related_products->the_post();
            wc_get_template_part('content', 'product');
        }

        echo '</div>';
        echo '</div>';
    }

    wp_reset_postdata();
}

add_action('woocommerce_after_single_product_summary', 'display_related_products_grid', 15);

function pedidos_del_mes_menu()
{
    add_submenu_page(
        'woocommerce',
        'Pedidos del Mes',
        'Pedidos del Mes',
        'manage_woocommerce',
        'pedidos-del-mes',
        'pedidos_del_mes_page'
    );
}
add_action('admin_menu', 'pedidos_del_mes_menu');

function pedidos_del_mes_page()
{
    global $wpdb;

    $fecha_un_mes_atras = date('Y-m-d H:i:s', strtotime('-1 month'));

    $pedidos = $wpdb->get_results($wpdb->prepare("
        SELECT id, customer_id, total_amount, date_created_gmt
        FROM wp_wc_orders
        WHERE status = 'wc-completed'
        AND date_created_gmt >= %s
    ", $fecha_un_mes_atras));

    echo '<div class="wrap">';
    echo '<h1>Pedidos Completados del Último Mes</h1>';

    if (empty($pedidos)) {
        echo '<p>No hay pedidos completados en el último mes.</p>';
    } else {
        echo '<table class="widefat fixed">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID del Pedido</th>';
        echo '<th>Nombre del Cliente</th>';
        echo '<th>Total del Pedido</th>';
        echo '<th>Fecha del Pedido</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($pedidos as $pedido) {
            $id_pedido = $pedido->id;

            $nombre_cliente = $wpdb->get_var($wpdb->prepare("
                SELECT meta_value 
                FROM wp_wc_orders_meta
                WHERE order_id = %d
                AND meta_key = '_billing_address_index'
            ", $id_pedido));

            if ($nombre_cliente) {
                $partes_nombre = explode(' ', $nombre_cliente);
                if (count($partes_nombre) >= 2) {
                    $nombre_completo = $partes_nombre[0] . ' ' . $partes_nombre[1];
                } else {
                    $nombre_completo = 'Nombre desconocido';
                }
            } else {
                $nombre_completo = 'Nombre desconocido';
            }

            $total_pedido = $pedido->total_amount;
            $fecha_pedido = date('d/m/Y', strtotime($pedido->date_created_gmt));

            echo '<tr>';
            echo '<td>' . esc_html($id_pedido) . '</td>';
            echo '<td>' . esc_html($nombre_completo) . '</td>';
            echo '<td>' . esc_html(number_format($total_pedido, 2)) . '</td>';
            echo '<td>' . esc_html($fecha_pedido) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }

    echo '</div>';
}

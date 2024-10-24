<?php
/*
Plugin Name: Custom WooCommerce Features
Description: Muestra un mensaje personalizado en la página de checkout basado en el valor total del carrito de WooCommerce.
Version: 1.0
Author: Tu Nombre
*/

// Salir si se accede directamente.
if (!defined('ABSPATH')) {
    exit;
}

// Verificar si WooCommerce está activo
function custom_woocommerce_features_check_woocommerce_active()
{
    return class_exists('WooCommerce');
}

// Hook adecuado para mostrar el mensaje en la página de checkout si WooCommerce está activo
add_action('woocommerce_before_checkout_form', 'mostrar_mensaje_checkout', 20);
add_action('storefront_before_content', 'mostrar_mensaje_checkout', 10);

function mostrar_mensaje_checkout()
{
    // Comprobar si WooCommerce está activo
    if (!custom_woocommerce_features_check_woocommerce_active()) {
        return; // Salir si WooCommerce no está activo
    }

    // Comprobar si estamos en la página de checkout
    if (!is_checkout()) {
        return; // Salir si no estamos en la página de checkout
    }

    // Asegurarse de que el carrito está disponible
    if (function_exists('WC') && WC()->cart) {
        // Obtener el total sin formato (número) del carrito
        $total_carrito = WC()->cart->get_total('float'); // El total en formato numérico

        // Definir un mensaje dinámico en función del monto total del carrito
        if ($total_carrito > 0) {
            echo '<p class="custom-message-checkout" style="text-align: center; margin: auto; max-width: 70%; padding: 20px 0; font-size: 2rem; color: blue; font-weight: bold;">Gracias por tu compra. Has gastado $' . number_format($total_carrito, 2) . ' hoy. ¡Obtén un descuento del 10% en tu próxima compra si vuelves pronto!</p>';
        } else {
            echo '<p class="custom-message-checkout" style="text-align: center; margin: auto; max-width: 70%; padding: 20px 0; font-size: 2rem; color: red; font-weight: bold;">Tu carrito está vacío. ¡Añade productos para aprovechar nuestras ofertas especiales!</p>';
        }
    } else {
        echo '<p class="custom-message-checkout" style="text-align: center; margin: auto; max-width: 70%; padding: 20px 0; font-size: 2rem; color: red; font-weight: bold;">Error: No se pudo obtener el total del carrito.</p>';
    }
}

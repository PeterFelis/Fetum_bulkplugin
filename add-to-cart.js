// filenaam: add-to-cart.js
// Onderdeel van de Fetum bulk plugin
// 2024


//jQuery(document).ready(function ($) {
// Functie om de totale prijs en het aantal te berekenen en weer te geven
function calculateAndDisplayTotal(product_id, box_qty) {
    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'calculate_total_price',
            product_id: product_id,
            box_qty: box_qty
        },
        success: function (response) {
            if (response.success) {
                jQuery('#total_price_display').html('Totaal aantal: ' + response.data.total_qty + ' - Totaal prijs: €' + response.data.total_price);
            } else {
                jQuery('#total_price_display').html('Er is een fout opgetreden.');
            }
        },
        error: function () {
            jQuery('#total_price_display').html('Er is een fout opgetreden bij het verbinden met de server.');
        }
    });
}
//});

function addToCart(product_id) {
    var box_qty = 1; // Aangenomen dat je altijd 1 doos toevoegt
    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'add_to_cart',
            product_id: product_id,
            quantity: box_qty
        },
        success: function (response) {
            if (response.success) {
                calculateAndDisplayTotal(product_id, box_qty); // Update deze functie indien nodig
            } else {
                alert('Er is een fout opgetreden bij het toevoegen aan de winkelwagen.');
            }
        }
    });
}

function removeFromCart(product_id) {
    var box_qty = -1; // Aangenomen dat je altijd 1 doos verwijdert
    jQuery.ajax({
        url: '/wp-admin/admin-ajax.php',
        type: 'POST',
        data: {
            action: 'remove_from_cart',
            product_id: product_id,
            quantity: box_qty
        },
        success: function (response) {
            if (response.success) {
                calculateAndDisplayTotal(product_id, box_qty); // Update deze functie indien nodig
            } else {
                alert('Er is een fout opgetreden bij het verwijderen uit de winkelwagen.');
            }
        }
    });
}




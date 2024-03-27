<?php
/*
Plugin Name: Fetum prijzen plugin
Description: Voeg aangepaste bulkprijzen toe aan WooCommerce-producten.
Version: 1.0
Author: Chat GTP & Peter Felis
Filenaam: Fetum-Bulk-plugin.php
   Gebruik de shortcode 
   [order_buttons id="123"]    --> bestel knoppen op de pagina
   [product_description id="123"]    --> beschrijving uit woocommerce op de pagina
*/


if (!defined('ABSPATH')) {
    exit;
}

// Voeg aangepaste velden toe aan de productpagina
add_action('woocommerce_product_options_pricing', 'add_bulk_pricing_fields');
function add_bulk_pricing_fields()
{

    //tonen aantal in een doos en prijs per stuk
    echo '<p class="form-field total-qty-display" 
            id="total_qty_1_box">
            <strong> Totaal:</strong> 
            <span class="qty-display"></span> 
            <span class="price-per-item" 
            id="price_per_item_1_box">
            </span>
            </p>';

    // invoeren totaal prijs voor 2 dozen
    woocommerce_wp_text_input(array(
        'id' => '_bulkprice_2_boxes',
        'label' => __('Prijs per doos bij 2 dozen', 'woocommerce'),
        'desc_tip' => 'true',
        'description' => __('Voer de prijs in voor 2 dozen', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => 'any',
            'min' => '0'
        )
    ));

    //toon totaal aantal in 2 dozen en prijs per stuk
    echo '<p class="form-field total-qty-display" 
            id="total_qty_2_boxes">
            <strong> Totaal:</strong> 
            <span class="qty-display"></span> 
            <span class="price-per-item" 
            id="price_per_item_2_boxes">
            </span>
            </p>';

    // Voeg hier eventueel meer velden toe voor andere hoeveelheden
    woocommerce_wp_text_input(array(
        'id' => '_bulkprice_4_boxes',
        'label' => __('Prijs per doos bij 4 dozen', 'woocommerce'),
        'desc_tip' => 'true',
        'description' => __('Voer de prijs in voor 4 dozen', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => 'any',
            'min' => '0'
        )
    ));


    echo '<p class="form-field total-qty-display" 
            id="total_qty_4_boxes">
            <strong> Totaal:</strong> 
            <span class="qty-display"></span> 
            <span class="price-per-item" 
            id="price_per_item_4_boxes">
            </span>
            </p>';

    // aantal per doos invoer
    woocommerce_wp_text_input(array(
        'id' => '_qty_per_box',
        'label' => __('Aantal per doos', 'woocommerce'),
        'desc_tip' => 'true',
        'description' => __('Voer het aantal items per doos in', 'woocommerce'),
        'type' => 'number',
        'custom_attributes' => array(
            'step' => '1',
            'min' => '1'
        )
    ));
}



// scripts voor de invoer bij de producten pagina
add_action('admin_enqueue_scripts', 'add_bulk_pricing_scripts');
function add_bulk_pricing_scripts()
{
    wp_enqueue_script('bulk-pricing-js', plugin_dir_url(__FILE__) . 'bulk-pricing.js', array('jquery'), '1.0', true);
    // Haal BTW-tarief op (voorbeeld: standaardtarief)
    $vat_rates = WC_Tax::get_rates(); // Dit haalt alle BTW-tarieven op
    $standard_rate = reset($vat_rates); // Neem het eerste (standaard) tarief

    // Stuur de BTW-gegevens naar je script
    wp_localize_script('bulk-pricing-js', 'bulkPricingData', array(
        'vat_rate' => isset($standard_rate['rate']) ? $standard_rate['rate'] : 0
    ));
}

// scripts om de js te laden om de bestellingen op te slaan in de winkelwagen
function enqueue_custom_scripts()
{
    wp_enqueue_script('add-to-cart-js', plugin_dir_url(__FILE__) . 'add-to-cart.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');





// weghalen van de actieprijs etc. gebruik ik toch niet
function my_plugin_enqueue_admin_styles()
{
    wp_enqueue_style('my-plugin-admin-style', plugin_dir_url(__FILE__) . 'bulk-pricing.css');
}

add_action('admin_enqueue_scripts', 'my_plugin_enqueue_admin_styles');





// Voeg een span toe om de hoeveelheid weer te geven naast de invoervelden
add_action('woocommerce_product_options_pricing', 'add_quantity_display_span');
function add_quantity_display_span()
{
    echo '<span class="quantity-display"></span>';
}


// Opslaan van aangepaste veldwaarden
add_action('woocommerce_admin_process_product_object', 'save_bulk_pricing_fields');
function save_bulk_pricing_fields($product)
{
    if (isset($_POST['_bulkprice_2_boxes'])) {
        $product->update_meta_data('_bulkprice_2_boxes', sanitize_text_field($_POST['_bulkprice_2_boxes']));
    }

    if (isset($_POST['_bulkprice_4_boxes'])) {
        $product->update_meta_data('_bulkprice_4_boxes', sanitize_text_field($_POST['_bulkprice_4_boxes']));
    }
    // Opslaan van andere velden
    if (isset($_POST['_qty_per_box'])) {
        $product->update_meta_data('_qty_per_box', sanitize_text_field($_POST['_qty_per_box']));
    }
}

// Aanpassen van prijzen op basis van bulkprijzen
add_action('woocommerce_init', 'custom_bulk_pricing_init');

function custom_bulk_pricing_init()
{
    add_filter('woocommerce_product_variation_get_price', 'custom_bulk_pricing', 10, 2);
}

function custom_bulk_pricing($price, $product)
{
    $bulk_price_2_boxes = $product->get_meta('_bulkprice_2_boxes', true);

    // Stel dat de klant 2 of meer dozen koopt
    $quantity_in_cart = get_quantity_in_cart($product->get_id());
    if ($quantity_in_cart >= 2) {
        if (!empty($bulk_price_2_boxes)) {
            $price = $bulk_price_2_boxes;
        }
    }

    if ($quantity_in_cart >= 4) {
        if (!empty($bulk_price_4_boxes)) {
            $price = $bulk_price_4_boxes;
        }
    }

    return $price;
}

function get_quantity_in_cart($product_id)
{
    $quantity = 0;
    $cart = WC()->cart->get_cart();
    foreach ($cart as $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            $quantity += $cart_item['quantity'];
        }
    }
    return $quantity;
}


// metabox op de productpagina om een link te kunnen maken naar een product buiten woo commerce om
function custom_product_page_url_metabox()
{
    add_meta_box(
        'custom_product_page_url',
        __('Aangepaste Productpagina URL', 'textdomain'),
        'custom_product_page_url_metabox_callback',
        'product',
        'side',
        'default'
    );
}

add_action('add_meta_boxes', 'custom_product_page_url_metabox');

function custom_product_page_url_metabox_callback($post)
{
    wp_nonce_field('custom_product_page_url_metabox', 'custom_product_page_url_metabox_nonce');
    $value = get_post_meta($post->ID, '_custom_product_page_url', true);
    echo '<input type="url" id="custom_product_page_url" name="custom_product_page_url" value="' . esc_attr($value) . '" style="width:100%">';
}

function save_custom_product_page_url_metabox($post_id)
{
    if (!isset($_POST['custom_product_page_url_metabox_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['custom_product_page_url_metabox_nonce'], 'custom_product_page_url_metabox')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['custom_product_page_url'])) {
        return;
    }
    $custom_url = sanitize_text_field($_POST['custom_product_page_url']);
    update_post_meta($post_id, '_custom_product_page_url', $custom_url);
}

add_action('save_post', 'save_custom_product_page_url_metabox');






// shortcode om bij produkt in te voegen om te kunnen bestellen

function add_order_buttons_shortcode($atts)
{
    $atts = shortcode_atts(array('id' => 0), $atts, 'order_buttons');
    $product_id = $atts['id'];

    if (!$product_id) return 'Geen geldig product ID opgegeven.';

    // Haal productgegevens op
    $product = wc_get_product($product_id);
    if (!$product) return 'Product niet gevonden.';

    // Haal de aangepaste velden op
    $qty_per_box = $product->get_meta('_qty_per_box', true);
    $price_1_box = $product->get_price(); // Reguliere prijs wordt gebruikt voor 1 doos
    $price_2_boxes = $product->get_meta('_bulkprice_2_boxes', true);
    $price_4_boxes = $product->get_meta('_bulkprice_4_boxes', true);



    ob_start();
?>

    <div class="bulk-order-table">
        <table>
            <tr>
                <th>Dozen
                <th>Aantal</th>
                <th>Prijs per stuk</th>
            </tr>
            <tr>
                <td>1</td>
                <td><?php echo $qty_per_box; ?></td>
                <td>€ <?php echo number_format($price_1_box / $qty_per_box, 2); ?></td>

            </tr>
            <tr>
                <td>2</td>
                <td> <?php echo $qty_per_box * 2; ?></td>
                <td>€ <?php echo number_format($price_2_boxes / ($qty_per_box * 2), 2); ?></td>

            </tr>

            <tr>
                <td>4</td>
                <td><?php echo $qty_per_box * 4; ?></td>
                <td>€<?php echo number_format($price_4_boxes / ($qty_per_box * 4), 2); ?></td>

            </tr>
        </table>
        <button onclick="addToCart(<?php echo $product_id; ?>)">1 doos toevoegen</button>
        <button onclick="removeFromCart(<?php echo $product_id; ?>)">1 doos verwijderen</button>
        <div id="total_price_display">Totaal: --</div>
    </div>

<?php
    return ob_get_clean();
}
add_shortcode('order_buttons', 'add_order_buttons_shortcode');





function add_to_cart_ajax_handler()
{
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity > 0) {
        WC()->cart->add_to_cart($product_id, $quantity);
        wp_send_json_success(array('message' => 'Product toegevoegd'));
    } else {
        wp_send_json_error(array('message' => 'Ongeldige hoeveelheid'));
    }
}
add_action('wp_ajax_add_to_cart', 'add_to_cart_ajax_handler');
add_action('wp_ajax_nopriv_add_to_cart', 'add_to_cart_ajax_handler');



function remove_from_cart_ajax_handler()
{
    $product_id = intval($_POST['product_id']);
    $cart = WC()->cart;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if ($cart_item['product_id'] == $product_id) {
            $new_quantity = max($cart_item['quantity'] - 1, 0); // Zorgt ervoor dat het aantal niet negatief wordt
            if ($new_quantity > 0) {
                $cart->set_quantity($cart_item_key, $new_quantity);
            } else {
                $cart->remove_cart_item($cart_item_key);
            }
            wp_send_json_success(array('message' => 'Product aangepast in winkelwagentje'));
            return; // Stop de loop na aanpassing
        }
    }

    wp_send_json_error(array('message' => 'Product niet gevonden in winkelwagen'));
}
add_action('wp_ajax_remove_from_cart', 'remove_from_cart_ajax_handler');
add_action('wp_ajax_nopriv_remove_from_cart', 'remove_from_cart_ajax_handler');



function calculate_total_price_ajax_handler()
{
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $box_qty_change = isset($_POST['box_qty']) ? intval($_POST['box_qty']) : 0; // Dit is +1 of -1

    if (!$product_id || $box_qty_change == 0) {
        wp_send_json_error('Ongeldige product ID of hoeveelheid.');
        return;
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product niet gevonden.');
        return;
    }

    // Haal het huidige aantal dozen in de winkelwagen voor dit product
    $current_boxes_in_cart = get_quantity_in_cart($product_id); // Deze functie moet het aantal DOZEN teruggeven, niet het aantal individuele items

    // Bereken het nieuwe totale aantal dozen in de winkelwagen
    $new_total_boxes = max($current_boxes_in_cart + $box_qty_change, 0); // Voorkom negatieve aantallen

    $qty_per_box = $product->get_meta('_qty_per_box', true);
    $bulk_prices = [
        1 => $product->get_price(), // Prijs voor 1 doos
        2 => $product->get_meta('_bulkprice_2_boxes', true), // Prijs voor 2 dozen
        4 => $product->get_meta('_bulkprice_4_boxes', true) // Prijs voor 4 dozen
    ];

    // Stel dat de prijs afhangt van de totale hoeveelheid dozen in de winkelwagen
    $price_per_box = $bulk_prices[1]; // Standaardprijs voor 1 doos
    if ($new_total_boxes >= 4 && isset($bulk_prices[4])) {
        $price_per_box = $bulk_prices[4];
    } elseif ($new_total_boxes >= 2 && isset($bulk_prices[2])) {
        $price_per_box = $bulk_prices[2];
    }

    $total_price = $price_per_box * $new_total_boxes; // Totale prijs gebaseerd op het nieuwe totaal aantal dozen
    $total_qty = $qty_per_box * $new_total_boxes; // Totale hoeveelheid items

    wp_send_json_success(array(
        'total_qty' => $total_qty,
        'total_price' => number_format($total_price, 2, '.', '')
    ));
}
add_action('wp_ajax_calculate_total_price', 'calculate_total_price_ajax_handler');
add_action('wp_ajax_nopriv_calculate_total_price', 'calculate_total_price_ajax_handler');




// shortcode die op de product pagina kan worden gebruikt om de productbeschrijving van woocommerce te tonen
// [product_description id="123"]

function show_product_description_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'id' => '', // Standaard is er geen ID
    ), $atts, 'product_description');

    if (empty($atts['id']) || !is_numeric($atts['id'])) {
        return 'Geen geldig product ID opgegeven.';
    }

    $product = wc_get_product($atts['id']);
    if (!$product) {
        return 'Product niet gevonden.';
    }

    $description = $product->get_description();
    $formatted_description = wpautop($description); // Zorgt ervoor dat linebreaks worden omgezet naar <p> en <br />

    return $formatted_description;
}
add_shortcode('product_description', 'show_product_description_shortcode');




// checkbox om cart aan of uit te zetten op een pagina
// hier wordt op de pagina een checkbox toegevoegd om de winkelwagen te verbergen en dit wordt weggeschreven
// de winkelwagen zit in de header template en wordt aan of uitgezet met javascript


function my_custom_meta_box()
{
    add_meta_box(
        'my_meta_box_id',           // ID van de meta box
        'Winkelwagen Zichtbaarheid', // Titel van de meta box
        'my_custom_meta_box_html',   // Callback functie die de HTML inhoud van de box genereert
        'page',                      // Post type waar de meta box verschijnt
        'side'                       // Positie van de meta box
    );
}

function my_custom_meta_box_html($post)
{
    $value = get_post_meta($post->ID, '_my_meta_key', true);
?>
    <label for="my_meta_box_field">Winkelwagen zichtbaar:</label>
    <select name="my_meta_box_field" id="my_meta_box_field" class="postbox">
        <option value="">Selecteer...</option>
        <option value="yes" <?php selected($value, 'yes'); ?>>Ja</option>
        <option value="no" <?php selected($value, 'no'); ?>>Nee</option>
    </select>
<?php
}

add_action('add_meta_boxes', 'my_custom_meta_box');

function my_save_postdata($post_id)
{
    if (array_key_exists('my_meta_box_field', $_POST)) {
        update_post_meta(
            $post_id,
            '_my_meta_key',
            $_POST['my_meta_box_field']
        );
    }
}

add_action('save_post', 'my_save_postdata');




//status line functie
// hiermee kan een lijn ingevoegd worden boven de pagina met een mededeling
// op het dashboard kan dit ingesteld worden.
// achtergrondkleur, tekstkleur, startdatum en einddatum kunnen ook ingesteld worden
// voor overzichtelijkheid wordt de lijn niet getoont als de admin pagina zichtbaar is 
// of bij het editen in oxygen.


function fetum_statuslijn_menu()
{
    add_submenu_page(
        'options-general.php',
        'Statuslijn Configuratie',
        'Statuslijn',
        'manage_options',
        'fetum-statuslijn',
        'fetum_statuslijn_options_page'
    );
}
add_action('admin_menu', 'fetum_statuslijn_menu');

function fetum_statuslijn_options_page()
{
    // Beveiliging: Voeg een nonce toe aan het formulier.
    $nonce = wp_nonce_field('fetum_statuslijn_save_options_action', 'fetum_statuslijn_nonce_field');

    // Ophalen van bestaande optiewaarden.
    $start_datum = get_option('fetum_statuslijn_start_datum', '');
    $eind_datum = get_option('fetum_statuslijn_eind_datum', '');
    $tekst = get_option('fetum_statuslijn_tekst', '');
    $balk_kleur = get_option('fetum_statuslijn_balk_kleur', '');
    $tekst_kleur = get_option('fetum_statuslijn_tekst_kleur', '');

    echo '<div class="wrap"><h2>Statuslijn Configuratie</h2>
    <form method="post" action="">
        ' . $nonce . '
        <table class="form-table">
            <tr valign="top">
            <th scope="row">Startdatum:</th>
            <td><input type="date" name="fetum_statuslijn_start_datum" value="' . esc_attr($start_datum) . '" /></td>
            </tr>
             
            <tr valign="top">
            <th scope="row">Einddatum:</th>
            <td><input type="date" name="fetum_statuslijn_eind_datum" value="' . esc_attr($eind_datum) . '" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Tekst:</th>
            <td><input type="text" name="fetum_statuslijn_tekst" value="' . esc_attr($tekst) . '" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Kleur van de balk:</th>
            <td><input type="color" name="fetum_statuslijn_balk_kleur" value="' . esc_attr($balk_kleur) . '" /></td>
            </tr>
            
            <tr valign="top">
            <th scope="row">Kleur van de tekst:</th>
            <td><input type="color" name="fetum_statuslijn_tekst_kleur" value="' . esc_attr($tekst_kleur) . '" /></td>
            </tr>
        </table>
        
        <p class="submit">
        <input type="submit" class="button-primary" value="Opslaan" />
        </p>
    </form></div>';
}

function fetum_statuslijn_save_options()
{
    if (
        !isset($_POST['fetum_statuslijn_nonce_field']) ||
        !wp_verify_nonce($_POST['fetum_statuslijn_nonce_field'], 'fetum_statuslijn_save_options_action')
    ) {
        return;
    }

    if (isset($_POST['fetum_statuslijn_start_datum'])) {
        update_option('fetum_statuslijn_start_datum', sanitize_text_field($_POST['fetum_statuslijn_start_datum']));
    }

    if (isset($_POST['fetum_statuslijn_eind_datum'])) {
        update_option('fetum_statuslijn_eind_datum', sanitize_text_field($_POST['fetum_statuslijn_eind_datum']));
    }

    if (isset($_POST['fetum_statuslijn_tekst'])) {
        update_option('fetum_statuslijn_tekst', sanitize_text_field($_POST['fetum_statuslijn_tekst']));
    }

    if (isset($_POST['fetum_statuslijn_balk_kleur'])) {
        update_option('fetum_statuslijn_balk_kleur', sanitize_hex_color($_POST['fetum_statuslijn_balk_kleur']));
    }

    if (isset($_POST['fetum_statuslijn_tekst_kleur'])) {
        update_option('fetum_statuslijn_tekst_kleur', sanitize_hex_color($_POST['fetum_statuslijn_tekst_kleur']));
    }
}

// Hook om de opties op te slaan wanneer het formulier wordt ingediend.
add_action('admin_init', 'fetum_statuslijn_save_options');


//toon de statuslijn, niet tonen op admin page
function is_oxygen_editor()
{
    // Vervang 'ct_builder' en 'true' door de daadwerkelijke Oxygen parameter en waarde indien bekend
    if (isset($_GET['ct_builder']) && $_GET['ct_builder'] == 'true') {
        return true;
    }
    return false;
}

function fetum_toon_statuslijn()
{
    // Controleer of we op een admin pagina zijn of in de Oxygen editor, zo ja, toon dan niets
    if (is_admin() || is_oxygen_editor()) {
        return;
    }

    $huidige_datum = date('Y-m-d');
    $start_datum = get_option('fetum_statuslijn_start_datum');
    $eind_datum = get_option('fetum_statuslijn_eind_datum');

    if ($huidige_datum >= $start_datum && $huidige_datum <= $eind_datum) {
        echo '<div style="background-color: ' . esc_attr(get_option('fetum_statuslijn_balk_kleur')) . '; color: ' . esc_attr(get_option('fetum_statuslijn_tekst_kleur')) . '; text-align: center; padding: 10px 0; position: fixed; width: 100%; z-index: 9999; top: 0; left: 0;">' . esc_html(get_option('fetum_statuslijn_tekst')) . '</div><script>document.body.style.paddingTop = "50px";</script>';
    }
}

add_action('wp_head', 'fetum_toon_statuslijn');

// start nieuw deel 
// hieronder de code die nodig is om een lijst te maken van categorieen en subcategorieen
// dit wordt de shortcode die we gaan gebruiken om een product te zoeken
// dit werkt samen met de js file prod-overzicht.js
// 21-03-2024

// Aangepaste mijn_categorieen_shortcode functie met containers voor subcategorieën en producten
// aanroepen met [mijn_categorieen doel_id="doelDiv"]
//
//maak een section op de pagina, in deze section 3 Divs maken, diplay flex   vertical 
//    Div 1 id=categorien. In deze div ook de shortcode zetten : [mijn_categorieen doel_id="categorien"]
//    Div 2 id=subcategorieContainer
//    Div 3 id=productenContainer    
//
// de shortcode bouwt nu het overzicht op. Het is preloaded met ajax (vandaar de prod-overzicht.js file)


function mijn_categorieen_shortcode($atts)
{
    $a = shortcode_atts(array(
        'doel_id' => 'doelDiv', // Standaard ID of via attribuut
    ), $atts);

    // Vooraf geladen data script
    $preloadDataScript = preload_categorie_data(true);

    // Ophalen van hoofdcategorieën voor initiële weergave
    $hoofdCategorieenArgs = array(
        'taxonomy' => 'product_cat',
        'parent' => 0
    );
    $hoofdCategorieen = get_terms($hoofdCategorieenArgs);

    // Opbouwen van de HTML output voor hoofdcategorieën
    $hoofdCategorieenHtml = '<div id="' . esc_attr($a['doel_id']) . '" class="hoofdCategorieenContainer">';
    foreach ($hoofdCategorieen as $cat) {
        $hoofdCategorieenHtml .= '<div class="hoofdCategorie" style="cursor: pointer; text-decoration: none;" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'" data-id="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</div>';
    }
    $hoofdCategorieenHtml .= '</div>';

    // Voeg de preload data script toe aan de output
    $output = $hoofdCategorieenHtml . $preloadDataScript;

    return $output;
}
add_shortcode('mijn_categorieen', 'mijn_categorieen_shortcode');

// Functie om de data vooraf te laden en in een script-tag te plaatsen
function preload_categorie_data($returnAsString = false)
{
    $hoofdCategorieenArgs = array(
        'taxonomy' => 'product_cat',
        'parent' => 0
    );
    $hoofdCategorieen = get_terms($hoofdCategorieenArgs);

    $data = array();

    foreach ($hoofdCategorieen as $cat) {
        $subCategorieenArgs = array(
            'taxonomy' => 'product_cat',
            'parent' => $cat->term_id
        );
        $subCategorieen = get_terms($subCategorieenArgs);
        $subCategorieenData = array();

        foreach ($subCategorieen as $subCat) {
            $productenArgs = array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'id',
                        'terms' => $subCat->term_id,
                    ),
                ),
            );
            $query = new WP_Query($productenArgs);
            $productenData = array();

            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $thumbnail_url = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');
                $productenData[] = array(
                    'id' => $product_id,
                    'thumbnail_url' => $thumbnail_url,
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                );
            }
            wp_reset_postdata();
            $subCategorieenData[] = array(
                'id' => $subCat->term_id,
                'name' => $subCat->name,
                'producten' => $productenData
            );
        }
        $data[$cat->term_id] = $subCategorieenData;
    }

    $script = '<script type="text/javascript">';
    $script .= 'var preloadData = ' . json_encode($data) . ';';
    $script .= '</script>';

    if ($returnAsString) {
        return $script;
    } else {
        echo $script;
    }
}


// Voeg de JavaScript toe aan de pagina
function enqueue_product_keuze_scripts()
{
    wp_enqueue_script('prod-overzicht-js', plugin_dir_url(__FILE__) . 'prod-overzicht.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_product_keuze_scripts');

// filenaam: bulk-pricing.js
// Onderdeel van de Fetum bulk plugin
// 2024


jQuery(document).ready(function ($) {
    $('label[for="_regular_price"]').text('Prijs per doos bij 1 doos');


    function updateQuantitiesAndPrices() {
        var qtyPerBox = parseFloat($('#_qty_per_box').val());
        var price1Box = parseFloat($('#_regular_price').val().replace(',', '.'));
        var price2Boxes = parseFloat($('#_bulkprice_2_boxes').val().replace(',', '.'));
        var price4Boxes = parseFloat($('#_bulkprice_4_boxes').val().replace(',', '.'));

        var qty1Box = qtyPerBox;
        var qty2Boxes = qtyPerBox * 2;
        var qty4Boxes = qtyPerBox * 4;

        var totalPrice1Box = price1Box * 1; // Voor 1 doos
        var totalPrice2Boxes = price2Boxes * 2; // Voor 2 dozen
        var totalPrice4Boxes = price4Boxes * 4; // Voor 4 dozen



        var vatRate = bulkPricingData.vat_rate; // BTW-tarief
        var vatMultiplier = (100 + parseFloat(vatRate)) / 100; // Bereken de vermenigvuldigingsfactor

        $('#total_qty_1_box .qty-display').html(qty1Box + ' stuks - Prijs per stuk: ' + (price1Box / qty1Box).toFixed(2)
            + '</br>Totaalprijs: ' + totalPrice1Box.toFixed(2) + '(ex btw) '
            + (totalPrice1Box * vatMultiplier).toFixed(2) + ' (incl btw)');

        $('#total_qty_2_boxes .qty-display').html(qty2Boxes + ' stuks - Prijs per stuk: ' + (price2Boxes / qty2Boxes).toFixed(2)
            + '</br>Totaalprijs: ' + totalPrice2Boxes.toFixed(2) + '(ex btw) '
            + (totalPrice2Boxes * vatMultiplier).toFixed(2) + ' (incl btw)');

        $('#total_qty_4_boxes .qty-display').html(qty4Boxes + ' stuks - Prijs per stuk: ' + (price4Boxes / qty4Boxes).toFixed(2)
            + '</br>Totaalprijs: ' + totalPrice4Boxes.toFixed(2) + '(ex btw) '
            + (totalPrice4Boxes * vatMultiplier).toFixed(2) + ' (incl btw)');


    }

    $('#_qty_per_box, #_regular_price, #_bulkprice_2_boxes, #_bulkprice_4_boxes').change(updateQuantitiesAndPrices);

    // Initialiseer de weergave
    updateQuantitiesAndPrices();
});



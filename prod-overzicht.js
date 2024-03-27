// filenaam: prod-overzicht.js
// Onderdeel van de Fetum bulk plugin
// de javascript die nodig is om een product te kiezen uit de lijst
// 2024


jQuery(document).ready(function ($) {
    var subCategorieContainerId = 'subCategorieContainer'; // ID van je subcategorie container
    var productenContainerId = 'productenContainer'; // ID van je producten container

    // Reset actieve status voor alle elementen behalve de huidige keten
    function resetActieveStatus() {
        $('.hoofdCategorie').removeClass('actief');
        $('.subCategorie').removeClass('actief');
    }

    // Voeg 'actief' class toe aan de huidige keten (hoofdcategorie, subcategorie)
    function markeerActieveKeten($element) {
        resetActieveStatus();
        $element.addClass('actief');
        // Markeer ook de bijbehorende hoofdcategorie als actief als het een subcategorie is
        if ($element.hasClass('subCategorie')) {
            var hoofdCatId = $element.closest('.hoofdCategorieContainer').data('hoofdcat-id');
            $('.hoofdCategorie[data-id="' + hoofdCatId + '"]').addClass('actief');
        }
    }

    // Bij hoveren over een hoofdcategorie
    $(document).on('mouseover', '.hoofdCategorie', function () {
        var $this = $(this);
        var categoryId = $this.data('id');
        markeerActieveKeten($this);

        var subCategorieenHtml = '';
        var subCategorieen = preloadData[categoryId] || [];

        subCategorieen.forEach(function (subCat) {
            subCategorieenHtml += '<div class="subCategorie" style="cursor: pointer;" data-id="' + subCat.id + '">' + subCat.name + '</div>';
        });

        $('#' + subCategorieContainerId).html(subCategorieenHtml);
        $('#' + productenContainerId).html(''); // Maak de producten container leeg
    });

    // Bij hoveren over een subcategorie
    $(document).on('mouseover', '.subCategorie', function () {
        var $this = $(this);
        var subCatId = $this.data('id');
        markeerActieveKeten($this);

        var productenHtml = '';
        var subCategorieen = [].concat.apply([], Object.values(preloadData));
        var subCategorie = subCategorieen.find(subCat => subCat.id == subCatId);

        if (subCategorie && subCategorie.producten) {
            subCategorie.producten.forEach(function (product) {
                productenHtml += '<div class="product-item" style="cursor: pointer; display: inline-block; margin: 5px;">';
                if (product.thumbnail_url) {
                    productenHtml += '<img src="' + product.thumbnail_url + '" alt="' + product.title + '" style="width:100px; height:auto;">';
                }
                productenHtml += '<p>' + product.title + '</p></div>';
            });
        }

        $('#' + productenContainerId).html(productenHtml);
    });

    // Extra logica voor interactie met product items (indien nodig)
    $(document).on('mouseover', '.product-item', function () {
        // Voorbeeld: markeer dit product-item met een specifieke stijl
        $('.product-item').removeClass('actief-product'); // Reset eerst alle product-items
        $(this).addClass('actief-product'); // Voeg 'actief-product' class toe aan het gehoverde product-item
    });
});


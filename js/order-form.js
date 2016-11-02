$(function () {
    window.mapReady = false;
    window.price = parseFloat($('#price').html());

    var App = {
        $cityText: $('#city-text'),
        $cityValue: $('#city-value'),
        $cityInput: $('#city-input'),
        $cityChange: $('#city-change'),
        $map: $('#map'),
        $address: $('#address'),
        $optionRussianMail: $('#option-russianmail'),
        $optionCourier: $('#option-courier'),
        $optionPvz: $('#option-pvz'),
        $quantity: $('.jSaleQty'),
        $price: $('#price'),
        $subtotal: $('#subtotal'),
        $deliveryCost: $('input[name="delivery_cost"]'),
        geoData: {
            cityName: '',
            cityId: null,
            x: null,
            y: null
        },
        deliveryType: 1,
        freeShippingCost: 2800.00,
        price: 980.00,
        currentPrice: 980.00,
        product: {
            length: 17.5,
            width: 12,
            height: 10,
            weight: 0.2
        },
        deliveryCost: null,
        deliveryPeriod: '',

        init: function () {
            $('#option-courier-info').hide();
            $('#option-pvz-info').hide();

            this.disableInputs();
            this.autocompleteOn();
            this.cityChangeOn();
            this.cityFocusOutOn();
            this.initYMaps();
            this.selectDeliveryOn();
            this.quantityChangeOn();
            this.calculateDelivery();
        },

        initYMaps: function () {
            var that = this;
            ymaps.ready(function () {
                ymaps.geolocation.get()
                    .then(function (result) {
                        var data = result.geoObjects.get(0).properties.getAll();

                        that.geoData = {
                            cityName: data.name,
                            x: data.boundedBy[0][0],
                            y: data.boundedBy[0][1]
                        };

                        that.processCity();
                    });
            });
        },

        processCity: function () {
            var that = this;

            if (that.geoData.cityName) {
                $.ajax({
                    url: '/jsale/cities.php',
                    data: {city: that.geoData.cityName},
                    dataType: 'json',

                    success: function (result) {
                        if (result.result) {
                            that.$cityText.text(that.geoData.cityName);
                            that.$cityValue.val(that.geoData.cityName);
                            that.geoData.cityId = result.id;

                            if (!window.mapReady) {
                                that.drawMap();
                            }

                            that.enableInputs();
                        } else {
                            that.$cityChange.toggle();
                            that.$cityText.toggle();
                            that.$cityInput.toggle().focus();
                        }
                    }
                });
            }
        },

        drawMap: function () {
            window.mapReady = true;

            var map = new ymaps.Map("map", {
                center: [this.geoData.x, this.geoData.y],
                zoom: 8,
                controls: ['zoomControl']
            });

            this.addPvz(map);
        },

        addPvz: function (map) {
            $.get('/jsale/cities.php', {pvz: true, id: this.geoData.cityId}, function (result) {
                result = $.parseJSON(result);

                var objectManager = new ymaps.ObjectManager({clusterize: true}),
                    points = {
                        type: 'FeatureCollection',
                        features: []
                    },
                    oldId = null;

                for (var index in result) {
                    points.features.push(
                        {
                            type: 'Feature',
                            id: result[index].Code,
                            geometry: {
                                type: 'Point',
                                coordinates: [result[index].coordY, result[index].coordX]
                            },
                            properties: {
                                hintContent: result[index].Name,
                                balloonContent: '<address><strong class="pvz-address">' + result[index].Address + '</strong></address><br/>' +
                                result[index].Phone + '<br/>' +
                                result[index].WorkTime
                            }
                        }
                    );
                }

                objectManager.add(points);
                objectManager.objects.events.add('click', function (e) {
                    var id = e.get('objectId');

                    return markClick(id);
                });

                var markClick = function (id) {
                    if (oldId) {
                        objectManager.objects.setObjectOptions(oldId, {
                            preset: 'islands#blueIcon'
                        });
                    }
                    objectManager.objects.setObjectOptions(id, {
                        preset: 'islands#redIcon'
                    });

                    var address = $('.pvz-address', objectManager.objects.getById(id).properties.balloonContent).html();
                    $('input[name="pvz-address"]').val(address);

                    return oldId = id;
                };

                map.geoObjects.add(objectManager);
            });
        },

        autocompleteOn: function () {
            var that = this;

            this.$cityInput.autocomplete({
                serviceUrl: '/jsale/cities.php',

                onSelect: function (suggestion) {
                    that.geoData.cityName = suggestion.value;
                    that.geoData.cityId = suggestion.id;
                    that.$cityValue.val(suggestion.value);
                    that.$cityText.text(suggestion.value);

                    $.get('https://geocode-maps.yandex.ru/1.x/?format=json&results=1&geocode=' + suggestion.value, {}, function (result) {
                        var coords = result.response.GeoObjectCollection.featureMember[0].GeoObject.Point.pos.split(' ');

                        that.geoData.y = coords[0];
                        that.geoData.x = coords[1];

                        that.$map.empty();
                        window.mapReady = false;
                        that.processCity();
                        that.calculateDelivery();
                        that.enableInputs();
                    });
                }
            });
        },

        cityChangeOn: function () {
            var that = this;

            that.$cityChange.on('click', function () {
                $(this).toggle();
                that.$cityText.toggle();
                that.$cityInput.toggle().val('').focus();
            });
        },

        cityFocusOutOn: function () {
            var that = this;

            this.$cityInput.blur(function () {
                that.$cityText.html($(this).val());
                that.$cityValue.val($(this).val());

                $(this).toggle();
                that.$cityText.toggle();
                that.$cityChange.toggle();

                that.disableInputs();
            });
        },

        selectDeliveryOn: function () {
            var that = this;

            $('input[name=order_delivery]').on('click', function () {
                switch ($(this).val()) {
                    case '0':
                        that.deliveryType = 0;

                        $('#courier-info').show();
                        $('#russianmail-info').hide();
                        $('#pvz-info').hide();

                        that.$address.show();
                        that.$map.hide();

                        that.calculateDelivery();
                        break;
                    case '1':
                        that.deliveryType = 1;

                        $('#russianmail-info').show();
                        $('#courier-info').hide();
                        $('#pvz-info').hide();

                        that.$address.show();
                        that.$map.hide();

                        that.calculateDelivery();
                        break;
                    case '2':
                        that.deliveryType = 2;

                        that.$map.show();
                        $('#pvz-info').show();
                        $('#russianmail-info').hide();
                        $('#courier-info')  .hide();
                        that.$address.hide();

                        that.calculateDelivery();
                        break;
                }
            });
        },

        calculateDelivery: function () {
            var quantity = parseInt(this.$quantity.val());

            var that = this;

            // 0 - courier, 2 - pvz
            if (this.geoData.cityId && ($.inArray(that.deliveryType, [0, 2]) !== -1)) {
                $.get('/jsale/cities.php', {
                    calc: true,
                    product: this.product,
                    quantity: quantity,
                    type: that.deliveryType,
                    id: this.geoData.cityId
                }, function (data) {
                    data = $.parseJSON(data);

                    that.deliveryCost = parseInt(data.result.price);
                    that.deliveryPeriod = data.result.deliveryPeriodMin + '-' + data.result.deliveryPeriodMax;
                    that.deliveryPeriod += data.result.deliveryPeriodMax < 5 ? ' дня' : ' дней';

                    if(that.currentPrice >= that.freeShippingCost) {
                        that.deliveryCost = 0;
                    }

                    var $container = that.deliveryType === 0 ? $('#courier-info') : $('#pvz-info');
                    that.renderTemplate($container);
                    that.$subtotal.html((that.currentPrice + that.deliveryCost).toFixed(2));
                });
            } else {
                if(that.currentPrice >= that.freeShippingCost) {
                    that.deliveryCost = 0;
                } else {
                    that.deliveryCost = 310;
                }

                that.deliveryPeriod = '5-7 дней';
                var $container = $('#russianmail-info');
                that.renderTemplate($container);
                that.$subtotal.html((that.currentPrice + that.deliveryCost).toFixed(2));
            }
        },

        quantityChangeOn: function () {
            var that = this;

            $('.jSaleQtyBtn').on('click', function (e) {
                e.preventDefault();

                var quantity = that.$quantity.val();

                if ($(this).hasClass('jSaleQtyMinus') && quantity > 1) {
                    that.currentPrice -= that.price;
                    that.$price.html(that.currentPrice.toFixed(2));
                    quantity--;
                    that.$quantity.val(quantity);
                }
                if ($(this).hasClass('jSaleQtyPlus')) {
                    that.currentPrice += that.price;
                    that.$price.html(that.currentPrice.toFixed(2));
                    quantity++;
                    that.$quantity.val(quantity);
                }

                that.calculateDelivery();
            });
        },

        disableInputs: function () {
            this.$optionCourier.parent('.deliv_type_info').addClass('tab-inactive');
            this.$optionPvz.parent('.deliv_type_info').addClass('tab-inactive');

            this.$optionCourier.prop('disabled', true);
            this.$optionPvz.prop('disabled', true).addClass('tab-inactive');
        },

        enableInputs: function () {
            this.$optionCourier.parent('.deliv_type_info').removeClass('tab-inactive');
            this.$optionPvz.parent('.deliv_type_info').removeClass('tab-inactive');

            this.$optionCourier.prop('disabled', false);
            this.$optionPvz.prop('disabled', false);
        },

        renderTemplate: function ($container) {
            var deliveryMessage = this.deliveryCost === 0 ? '<strong>Бесплатная доставка</strong>' : 'Стоимость доставки: <strong>' + this.deliveryCost + ' рублей</strong>';
            var $template = $('<p>' + deliveryMessage + '<br/>Срок доставки: <strong>' + this.deliveryPeriod + '</strong></p>');

            $container.children('.delivery-info').html('');
            $container.children('.delivery-info').append($template);
        }
    };
    App.init();

});
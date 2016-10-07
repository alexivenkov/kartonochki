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
        geoData: {
            cityName: '',
            cityId: null,
            x: null,
            y: null
        },

        init: function () {
            this.disableInputs();
            this.autocompleteOn();
            this.cityChangeOn();
            this.cityFocusOutOn();
            this.initYMaps();
            this.changeQunatityOn();
            this.selectDeliveryOn();
            this.quantityChangeOn();
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
                                balloonContent: '<address><strong>' + result[index].Address + '</strong></address><br/>' +
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

            that.$cityInput.blur(function () {
                $(this).toggle();
                that.$cityText.toggle();
                that.$cityChange.toggle();
            });
        },

        selectDeliveryOn: function () {
            var that = this;

            $('input[name=order_delivery]').on('click', function () {
                switch ($(this).val()) {
                    case '0':
                        that.$map.hide();
                        that.$address.show();
                        break;
                    case '1':
                        that.$map.hide();
                        that.$address.show();
                        break;
                    case '2':
                        that.$map.show();
                        that.$address.hide();
                        break;
                }
            });
        },

        calculateDelivery: function () {
            var quantity  = this.$quantity.val();

            $.get('/jsale/cities.php', {calc: true, quantity: quantity}, function (result) {
                console.log(123);
            });
        },

        changeQunatityOn: function() {
            var that = this;

            this.$quantity.on('change', function() {
                that.calculateDelivery();
            });
        },

        quantityChangeOn: function () {
            var that = this;

            $('.jSaleQtyBtn').on('click', function (e) {
                e.preventDefault();

                var quantity = that.$quantity.val();

                if ($(this).hasClass('jSaleQtyMinus') && quantity > 1) {
                    that.$price.html((parseFloat(that.$price.html()) - window.price).toFixed(2));
                    quantity--;
                    that.$quantity.val(quantity);
                }
                if ($(this).hasClass('jSaleQtyPlus')) {
                    that.$price.html((parseFloat(that.$price.html()) + window.price).toFixed(2));
                    quantity++;
                    that.$quantity.val(quantity);
                }
            });
        },

        disableInputs: function () {
            this.$optionCourier.prop('disabled', true);
            this.$optionPvz.prop('disabled', true);
        },

        enableInputs: function () {
            this.$optionCourier.prop('disabled', false);
            this.$optionPvz.prop('disabled', false);
        }

    };
    App.init();

});
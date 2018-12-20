/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the InnoExts Commercial License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://innoexts.com/commercial-license-agreement
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_CurrencyPricing
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Compound Discount Control
 */
var CompoundDiscountControl = Class.create(FormElementControl, {

    initialize : function($super, data) {
        if (!data) data = {};
        $super(data);
    }, 
    
    getTableElement : function() {
        return $(this.getBodyElement().select('table').first());
    }, 
            
    getTableBodyElement : function() {
        return $(this.getTableElement().select('tbody').first());
    }, 
    
    getRecalculateByElement : function() {
        return $(this.getActionsElement().select('.recalculate-by').first());
    }, 
    
    getRecalculateCurrencyElement : function() {
        return $(this.getActionsElement().select('.recalculate-currency').first());
    }, 
    
    onPriceChange : function(currentTrEl) {
        var recalculateByEl = this.getRecalculateByElement();
        if (recalculateByEl.checked) {
            var baseCurrency = this.getRecalculateCurrencyElement().getValue();
            if (currentTrEl) {
                var currentCurrency = null;
                var currentCurrencyEl = $(currentTrEl.select('.currency').first());
                if (currentCurrencyEl) {
                    currentCurrency = currentCurrencyEl.getValue();
                }
                if (baseCurrency && (baseCurrency === currentCurrency)) {
                    var basePrice = null;
                    var currentPriceEl = $(currentTrEl.select('.price').first());
                    if (currentPriceEl) {
                        basePrice = currentPriceEl.getValue();
                    }
                    if (basePrice) {
                        var tableBodyEl = this.getTableBodyElement();
                        if (tableBodyEl) {
                            tableBodyEl.select('tr').each(function(trEl) {
                                var currency = null;
                                var currencyEl = $(trEl.select('.currency').first());
                                if (currencyEl) {
                                    currency = currencyEl.getValue();
                                }
                                if (currency && (currentCurrency !== currency)) {
                                    var rate = null;
                                    var baseCurrencyRateEl = $(trEl.select('.' + baseCurrency + '-rate').first());
                                    if (baseCurrencyRateEl) {
                                        rate = baseCurrencyRateEl.getValue();
                                    }
                                    if (rate) {
                                        var priceEl = $(trEl.select('.price').first());
                                        var price = Number(basePrice) * Number(rate);
                                        if (priceEl && price) {
                                            priceEl.setValue(String(Math.round(price * 100) / 100));
                                        }
                                    }
                                }
                            });
                        }
                    }
                }
            }
        }
    }, 
    
    renderTable : function() {
        var self            = this;
        var tableBodyEl         = this.getTableBodyElement();
        if (tableBodyEl) {
            tableBodyEl.select('tr').each(function(trEl) {
                var priceEl = $(trEl.select('.price').first());
                if (priceEl) {
                    var useDefaultEl    = $(trEl.select('.use-default').first());
                    if (useDefaultEl) {
                        useDefaultEl.observe('click', function (el) {
                            if (useDefaultEl.checked) {
                                priceEl.disable();
                            } else {
                                priceEl.enable();
                            }
                        });
                    }
                    priceEl.observe('change', function () {
                        self.onPriceChange(trEl); 
                    });
                }
            });
        }
    }, 
    
    render : function() {
        this.renderTable();
    }
});

/**
 * Group Price Grid Control
 */
var GroupPriceGridControl = Class.create(GridControl, {
    
    initialize : function($super, data) {
        if (!data) data = {};
        $super(data);
    }, 
            
    getWebsiteElement : function(trEl) {
        return this.getColumnElementByName(trEl, 'website_id');
    }, 
    
    onChangeWebsite : function(trEl) {
        
    }, 
    
    renderItem : function(item) {
        var trEl = GridControl.prototype.renderItem.call(this, item);
        var websiteEl = this.getWebsiteElement(trEl);
        if (websiteEl) {
            var self = this;
            websiteEl.observe('change', function () { self.onChangeWebsite(trEl); });
            this.onChangeWebsite(trEl);
        }
        return trEl;
    }
});
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
        if (!data) {
            data = {};
        }
        if (data.actionElementId) {
            this.actionElementId    = data.actionElementId;
        }
        if (data.amountElementId) {
            this.amountElementId    = data.amountElementId;
        }
        $super(data);
        var self = this;
        this.getActionElement().observe('change', function () { self.render(); });
    }, 
    
    getActionElement : function() {
        return $(this.actionElementId);
    }, 
    
    getAmountElement : function() {
        return $(this.amountElementId);
    }, 
    
    isPercentMode : function() {
       var actionVal = this.getActionElement().getValue();
       return ((actionVal === 'by_percent') || (actionVal === 'to_percent')) ? true : false;
    }, 
    
    getTableElement : function() {
        return $(this.getBodyElement().select('table').first());
    }, 
            
    getTableHeadElement : function() {
        return $(this.getTableElement().select('thead').first());
    }, 
    
    getTableBodyElement : function() {
        return $(this.getTableElement().select('tbody').first());
    }, 
    
    toggleDefaultAmount : function(flag) {
        this.getTableHeadElement().select('tr').each(function (trEl) {
            trEl.select('th').each(function (thEl) {
                if (thEl.hasClassName('default-amount')) {
                    if (flag) {
                        thEl.show();
                    } else {
                        thEl.hide();
                    }
                }
            });
        });
        this.getTableBodyElement().select('tr').each(function (trEl) {
            trEl.select('td').each(function (tdEl) {
                if (tdEl.hasClassName('default-amount')) {
                    if (flag) {
                        tdEl.show();
                    } else {
                        tdEl.hide();
                    }
                }
            });
        });
    }, 
    
    hideDefaultAmount : function() {
        this.toggleDefaultAmount(false);
    }, 
    
    showDefaultAmount : function() {
        this.toggleDefaultAmount(true);
    }, 
    
    getRecalculateByElement : function() {
        return $(this.getActionsElement().select('.recalculate-by').first());
    }, 
    
    getRecalculateCurrencyElement : function() {
        return $(this.getActionsElement().select('.recalculate-currency').first());
    }, 
    
    onPriceChange : function(currentTrEl) {
        var recalculateByEl = this.getRecalculateByElement();
        if (!this.isPercentMode() && recalculateByEl.checked) {
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
        var isPercentMode   = this.isPercentMode();
        var defaultValue    = this.getAmountElement().getValue();
        var tableBodyEl     = this.getTableBodyElement();
        if (tableBodyEl) {
            tableBodyEl.select('tr').each(function(trEl) {
                var priceEl = $(trEl.select('.price').first());
                if (priceEl) {
                    var useDefault      = false;
                    var useDefaultEl    = $(trEl.select('.use-default').first());
                    if (useDefaultEl && useDefaultEl.getValue()) {
                        useDefault          = true;
                    }
                    if (useDefault) {
                        if (!isPercentMode) {
                            var defaultEl = $(trEl.select('.default').first());
                            if (defaultEl) {
                                defaultValue = defaultEl.getValue();
                            }
                        }
                        priceEl.setValue(
                            String(Math.round(defaultValue * 100) / 100)
                        );
                    }
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
        if (this.isPercentMode()) {
            this.hideActions();
            this.hideDefaultAmount();
        } else {
            this.showActions();
            this.showDefaultAmount();
        }
        this.renderTable();
    }
});
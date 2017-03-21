// ZIP Code    
jQuery.validator.addMethod("isZipCode", function(value, element) {   
    var tel = /^[0-9]{6}$/;
    return this.optional(element) || (tel.test(value));
}, "请正确填写您的邮政编码");
//Mobile phone number
jQuery.validator.addMethod("mobile", function(value, element) {   
    var reg=/^(((13[0-9]{1})|15[0-9]{1}|18[0-9]{1})+\d{8})$/i;
    return this.optional(element) || (reg.test(value));
}, "请正确填写您的手机号码");
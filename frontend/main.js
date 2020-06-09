Conekta.setPublicKey('key_Cf6xwVgweFHiqVvzixk5VEQ');
var conektaSuccessResponseHandler = function(token) {

    $("#conektaTokenId").val(token.id);
    jsPay();

};
//vamos hacer el caso del error
var conektaErrorResponseHandler = function(response) {
    var $form = $("#card-form");
    //mostramos el error
    alert(response.message_to_purchaser);
}
$(document).ready(function() {
    $("#card-form").submit(function(e) {
        e.preventDefault();
        var $form = $("#card-form");
        Conekta.Token.create($form, conektaSuccessResponseHandler, conektaErrorResponseHandler)

    });
})

function jsPay() {
    let params = $("#card-form").serialize();
    let url = "pay.php";
    $.post(url, params, function(data) {
        if (data == "1") {
            alert("El pago se realizo con exito :D ");
            jsClean();

        } else {
            alert(data);
        }

    });

}

function jsClean() {
    $(".form-control").prop("value", "");
    $("#conektaTokenId").prop("value", "");

}
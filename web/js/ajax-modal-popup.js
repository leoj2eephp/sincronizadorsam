$(function () {
    //get the click of modal button to create / update item
    //we get the button by class not by ID because you can only have one id on a page and you can
    //have multiple classes therefore you can have multiple open modal buttons on a page all with or without
    //the same link.
//we use on so the dom element can be called again if they are nested, otherwise when we load the content once it kills the dom element and wont let you load anther modal on click without a page refresh
    $(document).on('click', '.showModalButton', function () {
        //check if the modal is open. if it's open just reload content not whole modal
        //also this allows you to nest buttons inside of modals to reload the content it is in
        //the if else are intentionally separated instead of put into a function to get the 
        //button since it is using a class not an #id so there are many of them and we need
        //to ensure we get the right button and content. 
        if ($('#modal').data('bs.modal').isShown) {
            $('#modal').find('#modalContent')
                    .load($(this).attr('value'));
            //dynamiclly set the header for the modal
            document.getElementById('modalHeader').innerHTML = '<h4>' + $(this).attr('title') + '</h4>';
        } else {
            //if modal isn't open; open it and load content
            $('#modal').modal('show')
                    .find('#modalContent')
                    .load($(this).attr('value'));
            //dynamiclly set the header for the modal
            document.getElementById('modalHeader').innerHTML = '<h4>' + $(this).attr('title') + '</h4>';
        }
    });

    $("#modal").on("hidden.bs.modal", function () {
        $("#modalContent").html("<center><span class='fa fa-spinner fa-spin fa-3x text-info'></span></center>");
    });

    $(document).on("click", "#sync", function () {
        if (validaTotal()) {
            var indice = $("#indiceTabla").val();
            $.ajax({
                url: "/sincronizadorsam/web/modal/sync-sam-post",
                type: "post",
                data: $("#sam-modal").serialize(),
                dataType: "html",
                success: function (data) {
                    $("#modalContent").html(data);
                    if (data.indexOf("ok") != -1) {
                        $("#sync_" + indice).css("display", "none");
                    }
                }
            });
        } else {
            $("#errorMsg").html("Los montos de los vehículos deben coincidir con el valor del gasto.");
        }
    });

    $(document).on("submit", "#uploadDTE", function (e) {
        e.preventDefault();

        $.ajax({
            url: "/sincronizadorsam/web/modal/upload-dte",
            type: "post",
            data: new FormData(this),
            dataType: "html",
            processData: false, // tell jQuery not to process the data
            contentType: false, // tell jQuery not to set contentType
            success: function (data) {
                $("#modalContent").html(data);
//                setTimeout(function () {
//                    $('#modal').modal('hide');
//                }, 5000);
            }
        });
        // Es necesario retornar falso, cuando se previene el submit
        return false;
    });

});

function validaTotal() {
    var ok = true;
    if (parseInt($("#total").val()) !== parseInt($("#montoNeto").val())) {
        $("#spanSubtotal").css("color", "red");
        ok = false;
    } else {
        $("#spanSubtotal").css("color", "black");
    }
    return ok;
}
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
    })
    $(document).on("click", "#sync-remuneracion", function () {
        if (validaTotal()) {
            var indice = $("#indiceTabla").val();
            $.ajax({
                url: "/sincronizadorsam/web/modal/sync-sam-remuneraciones",
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

    $(document).on("click", "#sync-remu-manual", function () {
        if (validaTotal()) {
            $.ajax({
                url: "/sincronizadorsam/web/modal/sync-sam-remuneraciones",
                type: "post",
                data: $("#sam-modal").serialize(),
                dataType: "html",
                success: function (data) {
                    $("#modalContent").html(data);
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

    // función para agregar comentarios a un gasto de RindeGastos o Chipax
    $(document).on("blur", ".comentario", function() {
        var comentario = {
            id: $(this).attr("idComentario"),
            nroDoc: $(this).attr("nroDoc"),
            monto: $(this).attr("monto"),
            fecha: $(this).attr("fecha"),
            valor: $(this).val().trim()
        }
        $.post( "/sincronizadorsam/web/sincronizador/set-comentario", { comentario: JSON.stringify(comentario) })
        .done(function( data ) {
            Swal.fire(
                'INFORMACIÓN GRABADA',
                'Se guardó el comentario correctamente',
                'success'
            )
        })
        .fail(function() {
            Swal.fire(
                "ERROR AL MODIFICAR",
                "Ocurrió un problema al intentar modificar este comentario",
                "error"
            );
        });
    });

    // funciones para agregar vehículos al momento de sincronizar remuneración
    $(document).on("change", ".porcentaje", function() {
        let porcentaje = $(this).val();
        let montoNeto = parseInt($("#total").val());
    
        total = (montoNeto * porcentaje) / 100;
        // Se cambia esta línea, porque se agregaron más estilos y elementos, lo que hacía inútil el uso de next()
        //$(this).next(".valor").val(Math.round(total));
        $(this).closest(".row").next(".valor").val(Math.round(total));
    
        refrescarPrimerPorcentaje();
        calcularTotal();
    });
    
    $(document).on("change", ".valor", function() {
        let valor = $(this).val();
        let montoNeto = parseInt($("#total").val());
        
        let porcentaje = (valor * 100) / montoNeto;
        //$(this).prev(".porcentaje").val(Math.round(porcentaje));
        $(this).closest(".row").find(".porcentaje").val(Math.round(porcentaje));
    
        refrescarPrimerPorcentaje();
        calcularTotal();
    });
    
    $(document).on("click", ".delete-vehiculo", function() {
        $(this).closest("div.row.fila-vehiculos").remove();
        if ($(".fila-vehiculos").length == 0) {
            $(".primera-fila-vehiculos").find(".porcentaje").val(100);
            $(".primera-fila-vehiculos").find(".valor").val(parseInt($("#total").val()));
    
            $($(".primera-fila-vehiculos")[0]).addClass("fila-vehiculos");
            $($(".primera-fila-vehiculos")[0]).removeClass("primera-fila-vehiculos");
        } else {
            refrescarPrimerPorcentaje();
        }
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

function refreshSelect2Dataset() {
    $(".vehiculo").each(function(index, obj) {
        let dataKrajee = eval($(obj).data('krajee-select2'));
        $(obj).attr("id", "id_" + index);

        delete obj.dataset.select2Id;
        obj.dataset.select2Id = "id_" + index;

        //$(obj).select2(dataKrajee); Esta línea estaba provocando un error al clonar más de 1 vez
    });
}

function refrescarPrimerValor() {
    if ($(".primera-fila-vehiculos").length == 1) {
        var subtotal = 0;
        $(".fila-vehiculos").find(".valor").each(function(index, obj) {
            subtotal += parseInt($(obj).val());
        });

        let montoNeto = parseInt($("#total").val());
        $(".primera-fila-vehiculos").find(".valor").val(montoNeto - subtotal);
    }
}

function refrescarPrimerPorcentaje() {
    if ($(".primera-fila-vehiculos").length == 1) {
        var subtotal = 0;
        $(".fila-vehiculos").find(".porcentaje").each(function(index, obj) {
            subtotal += parseInt($(obj).val());
        });

        $(".primera-fila-vehiculos").find(".porcentaje").val(100 - subtotal);
        refrescarPrimerValor();
    }
}

function refrescarSubtotales() {
    let montoNeto = parseInt($("#total").val());
    let cantidadVehiculos = $(".vehiculo").length;
    let subNetos = Math.trunc(montoNeto / cantidadVehiculos);

    $(".fila-vehiculos").find(".valor").val(subNetos);
    let restanteDivision = parseInt(montoNeto - (subNetos * cantidadVehiculos));
    $(".primera-fila-vehiculos").find(".valor").val(subNetos + restanteDivision);

    let porcentajeDividido = Math.trunc(100 / cantidadVehiculos);
    $(".fila-vehiculos").find(".porcentaje").val(porcentajeDividido);
    let sumaPorcentajes = porcentajeDividido * (cantidadVehiculos - 1);
    $(".primera-fila-vehiculos").find(".porcentaje").val(100 - sumaPorcentajes);

    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    $(".valor").each(function(index, obj) {
        total += parseInt($(obj).val());
    });

    $("#spanTotal").html("TOTAL: $ " + total.toLocaleString('de-DE', { style: 'currency', currency: 'CLP' }));
    $("#total").val(total);
    validaTotal();
}
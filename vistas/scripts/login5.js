$("#frmAcceso").on('submit', function (e) {
    e.preventDefault();
    logina = $("#logina").val();
    clavea = $("#clavea").val();

    console.log("hace la validación =)");

    $.post("../ajax/usuario.php?op=verificar", { "logina": logina, "clavea": clavea },
        function (data) {
            console.log(data);

            if (data == 0) {
                bootbox.alert("Su usuario está desactivado, comuníquese con el administrador.");
            } else if (data == 1) {
                bootbox.alert("El usuario no se encuetnra disponible, comuníquese con el administrador.");
            } else if (data != "null") {
                $(location).attr("href", "escritorio.php");
            } else {
                bootbox.alert("Usuario y/o contraseña incorrectos.");
            }
        });
})
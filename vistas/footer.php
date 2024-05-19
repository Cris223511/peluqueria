    <footer class="main-footer">
      <div class="pull-right hidden-xs">
        <b>Version</b> 4.0.0
      </div>
      <strong>Copyright &copy; 2024 <a href="escritorio.php" style="color: #002a8e;">Sistema de Peluquería</a>.</strong> Todos los derechos reservados.
    </footer>
    <!-- jQuery -->
    <script src="../public/js/jquery-3.1.1.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="../public/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../public/js/app.min.js"></script>
    <!-- Lightbox JS -->
    <script src="../public/glightbox/js/glightbox.min.js"></script>

    <!-- DATATABLES -->
    <script src="../public/datatables/jquery.dataTables.min.js"></script>
    <script src="../public/datatables/dataTables.buttons.min.js"></script>
    <script src="../public/datatables/buttons.html5.min.js"></script>
    <script src="../public/datatables/buttons.colVis.min.js"></script>
    <script src="../public/datatables/jszip.min.js"></script>
    <script src="../public/datatables/pdfmake.min.js"></script>
    <script src="../public/datatables/vfs_fonts.js"></script>

    <script src="../public/js/bootbox.min.js"></script>
    <script src="../public/js/bootstrap-select.min.js"></script>

    <script>
      function inicializeGLightbox() {
        const glightbox = GLightbox({
          selector: '.glightbox'
        });

        const galelryLightbox = GLightbox({
          selector: ".galleria-lightbox",
        });
      }
    </script>

    <script>
      $('[data-toggle="popover"]').popover();
    </script>

    <script>
      function setTitleFromBoxTitle(titleElement) {
        const title = $(titleElement).contents().filter(function() {
          return this.nodeType === 3;
        }).text().trim();

        const fullTitle = title.replace(/\b([a-zA-ZáéíóúÁÉÍÓÚ]+)/g, function(match) {
          return match.charAt(0).toUpperCase() + match.slice(1).toLowerCase();
        });

        document.title = fullTitle;
      }

      setTitleFromBoxTitle('.box-title');
    </script>

    <script>
      function formatHora(hora) {
        let partes = hora.split(':');
        let h = parseInt(partes[0], 10);
        let m = parseInt(partes[1], 10);
        let s = parseInt(partes[2], 10);

        let ampm = h >= 12 ? 'p.m.' : 'a.m.';
        h = h % 12 || 12;
        h = h < 10 ? '0' + h : h;
        m = m < 10 ? '0' + m : m;

        return h + ':' + m + ' ' + ampm;
      }

      function formatFecha(fecha) {
        const meses = [
          "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
          "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];

        let partes = fecha.split('-');
        let dia = partes[0];
        let mes = meses[parseInt(partes[1]) - 1];
        let anio = partes[2];

        return dia + " de " + mes + " del " + anio;
      }
    </script>

    <script>
      function capitalizarPalabras(palabra) {
        return palabra.charAt(0).toUpperCase() + palabra.slice(1);
      }

      function capitalizarTodasLasPalabras(palabra) {
        return palabra.toUpperCase();
      }

      function minusTodasLasPalabras(palabra) {
        return palabra.toLowerCase();
      }

      const thElements = document.querySelectorAll("#tblarticulos th, #tbldetalles th, #tbldetalles2 th, #tbllistado th, #tbltrabajadores th");

      thElements.forEach((e) => {
        e.textContent = e.textContent.toUpperCase();
        e.classList.add('nowrap-cell');
      });

      const boxTitle = document.querySelectorAll(".box-title");

      boxTitle.forEach((e) => {
        e.childNodes.forEach((node) => {
          if (node.nodeType === Node.TEXT_NODE) {
            node.textContent = node.textContent.toUpperCase();
          }
        });
      });

      function changeValue(dropdown) {
        var option = dropdown.options[dropdown.selectedIndex].value;

        console.log(option);

        $("#num_documento").val("");

        if (option == 'DNI') {
          setMaxLength('#num_documento', 8);
          setMaxLength('#num_documento4', 8);
        } else if (option == 'CEDULA') {
          setMaxLength('#num_documento', 10);
          setMaxLength('#num_documento4', 10);
        } else if (option == 'CARNET DE EXTRANJERIA') {
          setMaxLength('#num_documento', 20);
          setMaxLength('#num_documento4', 20);
        } else {
          setMaxLength('#num_documento', 11);
          setMaxLength('#num_documento4', 11);
        }
      }

      function setMaxLength(fieldSelector, maxLength) {
        $(fieldSelector).attr('maxLength', maxLength);
      }

      $('#mostrarClave').click(function() {
        var claveInput = $('#clave');
        var ojitoIcon = $('#mostrarClave i');

        if (claveInput.attr('type') === 'password') {
          claveInput.attr('type', 'text');
          ojitoIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
          claveInput.attr('type', 'password');
          ojitoIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
      });

      $(document).on('draw.dt', function(e, settings) {
        if ($(settings.nTable).is('#tbllistado') || $(settings.nTable).is('#tblarticulos')) {
          const table = $(settings.nTable).DataTable();
          if (table.rows({
              page: 'current'
            }).count() > 0) {
            inicializeGLightbox();
          }
        }
      });

      $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url.includes("op=listar") || settings.url.includes("op=guardaryeditar") || settings.url.includes("op=desactivar") || settings.url.includes("op=activar") || settings.url.includes("op=eliminar")) {
          $(".modal-footer .btn-primary").removeClass("btn-primary").addClass("btn-bcp");
        }
      });

      // Evento click en el documento
      $(document).on('click', function(e) {
        // Comprobar si el clic fue fuera del popover
        if ($(e.target).closest('[data-toggle="popover"]').length === 0) {
          // Cerrar el popover
          $('[data-toggle="popover"]').popover('hide');
        }
      });

      // Evitar que el popover se cierre al hacer clic dentro de él
      $(document).on('click', '.popover', function(e) {
        e.stopPropagation();
      });

      function evitarNegativo(e) {
        if (e.key === "-")
          e.preventDefault();
      }
    </script>

    <script>
      $('.selectpicker').selectpicker({
        noneResultsText: 'No se encontraron resultados.'
      });
    </script>

    <script>
      function evitarCaracteresEspecialesCamposNumericos() {
        var camposNumericos = document.querySelectorAll('input[type="number"]:not(#ganancia)');

        camposNumericos.forEach(function(campo) {
          campo.addEventListener('keydown', function(event) {
            var teclasPermitidas = [46, 8, 9, 27, 13, 110, 190, 37, 38, 39, 40, 17, 82]; // ., delete, tab, escape, enter, flechas, Ctrl+R

            // Permitir Ctrl+C, Ctrl+V, Ctrl+X y Ctrl+A
            if ((event.ctrlKey || event.metaKey) && (event.which === 67 || event.which === 86 || event.which === 88 || event.which === 65)) {
              return;
            }

            // Permitir Ctrl+Z y Ctrl+Alt+Z
            if ((event.ctrlKey || event.metaKey) && event.which === 90) {
              if (!event.altKey) {
                // Permitir Ctrl+Z
                return;
              } else if (event.altKey) {
                // Permitir Ctrl+Alt+Z
                return;
              }
            }

            if (teclasPermitidas.includes(event.which) || (event.which >= 48 && event.which <= 57) || (event.which >= 96 && event.which <= 105) || event.which === 190 || event.which === 110) {
              // Si es una tecla permitida o numérica, no hacer nada
              return;
            } else {
              event.preventDefault(); // Prevenir cualquier otra tecla no permitida
            }
          });
        });
      }

      evitarCaracteresEspecialesCamposNumericos();
    </script>

    <script>
      function convertirMayus(inputElement) {
        if (typeof inputElement.value === 'string') {
          inputElement.value = inputElement.value.toUpperCase();
        }
      }

      function convertirMinus(inputElement) {
        if (typeof inputElement.value === 'string') {
          inputElement.value = inputElement.value.toLowerCase();
        }
      }

      function capitalizarPrimeraLetra(cadena) {
        return cadena.charAt(0).toUpperCase() + cadena.slice(1).toLowerCase();
      }

      function onlyNumbersAndMaxLenght(input) {
        let newValue = "";

        if (input.value.length > input.maxLength)
          newValue = input.value.slice(0, input.maxLength);

        newValue = input.value.replace(/\D/g, '');
        input.value = newValue;
      }

      function onlyNumbers(input) {
        let newValue = "";
        newValue = input.value.replace(/\D/g, '');
        input.value = newValue;
      }

      function validarNumeroDecimal(input, maxLength) {
        input.value = input.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');

        if (input.value.length > maxLength) {
          input.value = input.value.slice(0, maxLength);
        }
      }
    </script>

    <script>
      function restrict(input) {
        var prev = input.getAttribute("data-prev");
        prev = (prev != '') ? prev : '';
        if (Math.round(input.value * 100) / 100 != input.value) {
          input.value = prev;
        }
        input.setAttribute("data-prev", input.value);
      }

      function aplicarRestrictATodosLosInputs() {
        var camposNumericos = document.querySelectorAll('input[type="number"]');
        camposNumericos.forEach(function(campo) {
          campo.addEventListener('input', function(event) {
            restrict(event.target);
          });
        });
      }

      aplicarRestrictATodosLosInputs();
    </script>

    <script>
      function generarSiguienteCorrelativo(numero) {
        console.log("Número recibido por el servidor: ", numero);

        let numFormat = numero.trim();
        let num = isNaN(parseInt(numFormat, 10)) ? 0 : parseInt(numFormat, 10);

        console.log("Número a incrementar: ", num);
        num++;

        let siguienteCorrelativo = num < 10000 ? num.toString().padStart(5, '0') : num.toString();

        console.log("Número incrementado a setear: ", siguienteCorrelativo);
        return siguienteCorrelativo;
      }

      function limpiarCadena(cadena) {
        let cadenaLimpia = cadena.trim();
        cadenaLimpia = cadenaLimpia.replace(/^[\n\r]+/, '');
        return cadenaLimpia;
      }

      function formatearNumero() {
        var campos = ["#codigo"];

        campos.forEach(function(campo) {
          let numValor = $(campo).val();
          if (typeof numValor !== 'undefined') {
            numValor = numValor.trim();
            let num = numValor === '' ? siguienteCorrelativo || 0 : parseInt(numValor, 10);
            let numFormateado = num < 10000 ? num.toString().padStart(5, '0') : num.toString();
            $(campo).val(numFormateado);
          }
        });
      }
    </script>

    <script>
      function inicializegScrollingCarousel() {
        $(".carousel .items").gScrollingCarousel();
        $(".carousel-three .items").gScrollingCarousel({
          mouseScrolling: false,
          draggable: true,
          snapOnDrag: false,
          mobileNative: false,
        });
      }

      function buttonsScrollingCarousel() {
        document.querySelectorAll('.carousel-three').forEach(carousel => {
          var leftElements = carousel.querySelectorAll('.jc-left');
          var rightElements = carousel.querySelectorAll('.jc-right');

          leftElements.forEach((element, index) => {
            if (index > 0) element.remove();
          });

          rightElements.forEach((element, index) => {
            if (index > 0) element.remove();
          });

          inicializegScrollingCarousel();
        });
      }
    </script>

    <?php
    if ($_SESSION["cargo"] == "cajero") {
      // echo '<script>
      //         $(document).ajaxSuccess(function(event, xhr, settings) {
      //           if (!$("#lCierres").hasClass("active")) {
      //             $(".dt-buttons").hide();
      //           }
      //         });
      //       </script>';
    } elseif ($_SESSION["cargo"] != "superadmin" && $_SESSION["cargo"] != "admin_total" && $_SESSION["cargo"] != "admin") {
      echo '<script>
              $(document).ajaxSuccess(function(event, xhr, settings) {
                $(".dt-buttons").hide();
              });
            </script>';
    }
    ?>

    <script>
      $('.selectpicker').selectpicker({
        dropupAuto: false
      });
    </script>

    <script>
      $(document).on('show.bs.modal', function(event) {
        const modal = $(event.target);

        if (modal.hasClass('bootbox') && modal.hasClass('bootbox-confirm')) {
          modal.find('.modal-footer .btn-default').text('Cancelar');
          modal.find('.modal-footer .btn-primary').text('Aceptar');
        }

        const okButton = modal.find('.modal-footer button[data-bb-handler="ok"]');
        if (okButton.length) {
          okButton.text('Aceptar').removeClass('btn-default').addClass('btn-bcp');

          // Agregar controlador de eventos para la tecla Enter
          $(document).on('keypress', function(e) {
            if (e.which === 13 && modal.hasClass('in')) {
              // Verificar si el modal está abierto y la tecla presionada es Enter
              okButton.click(); // Hacer clic en el botón Aceptar
            }
          });
        }
      });
    </script>

    <script>
      function mostrarOcultarColumnaAlmacen() {
        <?php
        // Verificar el rol del usuario
        $mostrarColumna = ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total");

        // Script JavaScript para mostrar u ocultar la columna
        $script = '';

        if (!$mostrarColumna) {
          $script .= '
            // Obtener la tabla
            var tabla = document.getElementById("detallesProductosPrecuenta");

            // Obtener el índice de la columna del almacén
            var indiceColumnaAlmacen = 2;

            // Ocultar la cabecera
            tabla.rows[0].cells[indiceColumnaAlmacen].style.display = "none";

            // Ocultar el cuerpo
            for (var i = 0; i < tabla.rows.length; i++) {
                tabla.rows[i].cells[indiceColumnaAlmacen].style.display = "none";
            }
        ';
        }

        // Imprimir el script JavaScript
        echo $script;
        ?>
      }
    </script>

    </body>

    </html>
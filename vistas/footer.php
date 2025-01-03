    <footer class="main-footer">
      <div class="pull-right hidden-xs">
        <b>Version</b> 4.0.2
      </div>
      <strong>Copyright &copy; 2024 <a href="escritorio.php" style="color: #002a8e;">Sistema de ventas</a>.</strong> Todos los derechos reservados.
    </footer>
    <!-- jQuery -->
    <script src="../public/js/jquery-3.1.1.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="../public/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../public/js/app.min.js"></script>
    <!-- Quagga JS -->
    <script src="../public/js/quagga.min.js"></script>
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
      function agregarBuscadorColumna(tabla, columnaIndex, placeholder) {
        $('.dataTables_filter').append(
          $('<input>', {
            type: 'text',
            placeholder: placeholder,
            style: 'margin-left: 10px;',
          }).on('keyup change', function() {
            let valor = this.value;
            let regex = valor ? `^.*${valor.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1')}.*$` : '';
            tabla.column(columnaIndex).search(regex, true, false).draw();
          })
        );
      }
    </script>

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
      $('#imagen, #imagen2').on('change', function() {
        const file = this.files[0];
        const maxSizeMB = 3;
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/bmp'];

        // Validar tamaño
        if (file.size > maxSizeBytes) {
          bootbox.alert(`El archivo es demasiado grande. El tamaño máximo permitido es de ${maxSizeMB} MB.`);
          $(this).val('');
          $('#imagenmuestra').attr('src', '').hide();
          return;
        }

        // Validar tipo
        console.log("el file type es =) =>", file.type);
        if (!allowedTypes.includes(file.type)) {
          bootbox.alert('El archivo debe ser una imagen de tipo JPG, JPEG, PNG, JFIF o BMP.');
          $(this).val('');
          $('#imagenmuestra').attr('src', '').hide();
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          $('#imagenmuestra').attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
      });
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
      $(document).on('hidden.bs.modal', '.modal', function() {
        // Buscar el botón que abrió este modal
        const triggeringElement = $('[data-target="#' + $(this).attr('id') + '"], [href="#' + $(this).attr('id') + '"]');

        // Si existe, mover el foco al botón; de lo contrario, moverlo al body
        if (triggeringElement.length > 0) {
          triggeringElement.focus();
        } else {
          $('body').focus();
        }
      });
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
        return palabra ? palabra.toUpperCase() : palabra;
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
        $('.dataTables_filter input[type="search"]').attr('placeholder', 'Buscar en toda la tabla.').css({
          'font-weight': '500'
        });

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

      function nowrapCell() {
        ["#tbllistado", "#detalles", "#tbllistado2", "#tbllistado3", "#tblarticulos", "#tbldetalles", "#tbldetalles2", "#tbllistado_categorias", "#tbllistado_locales", "#tbllistado_marcas", "#tbllistado_medidas"].forEach(selector => {
          addClassToCells(selector, "nowrap-cell");
        });
      }

      function addClassToCells(selector, className) {
        var table = document.querySelector(selector);

        if (!table) return;

        var columnIndices = Array.from(table.querySelectorAll("th")).reduce((indices, th, index) => {
          if (["CLIENTE", "PROVEEDOR", "NOMBRE", "NOMBRES", "CONCEPTO", "DESCRIPCIÓN", "DESCRIPCIÓN DEL LOCAL", "ALMACÉN"].includes(th.innerText.trim())) {
            indices.push(index);
          }
          return indices;
        }, []);

        table.querySelectorAll("td, th").forEach((cell, index) => {
          var cellIndex = index % table.rows[0].cells.length;
          if (!columnIndices.includes(cellIndex)) {
            cell.classList.add(className);
          }
        });
      }

      $(document).on('draw.dt', function(e, settings) {
        if ($(settings.nTable).is('#tbllistado') || $(settings.nTable).is('#detalles') || $(settings.nTable).is('#tbllistado2') || $(settings.nTable).is('#tbllistado3') || $(settings.nTable).is('#tblarticulos') || $(settings.nTable).is('#tbldetalles') || $(settings.nTable).is('#tbldetalles2') || $(settings.nTable).is('#tbllistado_locales')) {
          const table = $(settings.nTable).DataTable();
          if (table.rows({
              page: 'current'
            }).count() > 0) {
            inicializeGLightbox();
            nowrapCell();
          }
        }
      });

      $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.url.includes("op=listar") || settings.url.includes("op=listarDetalle")) {
          nowrapCell();
        }
      });
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
        if (typeof cadena === 'object' && cadena !== null) {
          return cadena;
        }

        let cadenaLimpia = cadena.trim();
        cadenaLimpia = cadenaLimpia.replace(/^[\n\r]+/, '');
        return cadenaLimpia;
      }

      function formatearNumeroCorrelativo() {
        console.log("entro =)");
        var campos = ["#codigo", "#cod_part_2"];

        campos.forEach(function(campo) {
          let numValor = $(campo).val();
          if (typeof numValor !== 'undefined') {
            if (numValor === "") {
              idlocal = $('select[data-local="0"]').val();
              console.log("el idlocal actual es =) =>", idlocal);
              actualizarCorrelativoProducto(idlocal);
              return;
            }

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
    if ($_SESSION["cargo"] != "superadmin" && $_SESSION["cargo"] != "admin_total" && $_SESSION["cargo"] != "admin") {
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
      function mostrarOcultarPrecioCompraCampo() {
        <?php
        $mostrarColumna = ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total");

        if (!$mostrarColumna) {
          echo '
            // Si no es superadmin, ocultar el campo y ajustar el tamaño de las columnas
            $("#precio_compra_campo").hide();
            $("#precio_venta").closest(".form-group").removeClass("col-lg-3").addClass("col-lg-4");
            $("#precio_venta_mayor").closest(".form-group").removeClass("col-lg-3").addClass("col-lg-4");
            $("#ganancia").closest(".form-group").removeClass("col-lg-3").addClass("col-lg-4");
          ';
        } else {
          echo '
            // Si es superadmin, mostrar el campo y restaurar el tamaño de las columnas
            $("#precio_compra_campo").show();
            $("#precio_venta").closest(".form-group").removeClass("col-lg-4").addClass("col-lg-3");
            $("#precio_venta_mayor").closest(".form-group").removeClass("col-lg-4").addClass("col-lg-3");
            $("#ganancia").closest(".form-group").removeClass("col-lg-4").addClass("col-lg-3");
          ';
        }
        ?>
      }
    </script>


    <script>
      function mostrarOcultarColumnaAlmacen() {
        <?php
        $mostrarColumna = ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total");

        $script = '';

        if (!$mostrarColumna) {
          $script .= '
            var tabla = document.getElementById("detallesProductosPrecuenta");
            var indiceColumnaAlmacen = 2;

            tabla.rows[0].cells[indiceColumnaAlmacen].style.display = "none";

            for (var i = 0; i < tabla.rows.length; i++) {
                tabla.rows[i].cells[indiceColumnaAlmacen].style.display = "none";
            }
        ';
        }

        echo $script;
        ?>
      }
    </script>

    <script>
      var columnasAocultar = [
        "P. compra",
        "Ganancia"
      ];

      function ocultarColumnasPorNombre(tablaId, nombresColumnas) {
        <?php
        $mostrarColumna = ($_SESSION["cargo"] == "superadmin" || $_SESSION["cargo"] == "admin_total");

        if (!$mostrarColumna) {
          echo '
            // Iteramos sobre cada nombre de columna que queremos ocultar
            nombresColumnas.forEach(function(nombreColumna) {
                // Buscar el índice de la columna en el thead
                $("#" + tablaId + " th").each(function(index) {
                    if ($(this).text().trim().toLowerCase() === nombreColumna.toLowerCase()) {
                        // Si el texto del encabezado coincide, obtenemos el índice
                        var columnIndex = index + 1; // +1 porque :nth-child es 1-based

                        // Ocultamos la columna en thead, tbody y tfoot usando el índice
                        $("#" + tablaId + " th:nth-child(" + columnIndex + ")").css("display", "none");
                        $("#" + tablaId + " td:nth-child(" + columnIndex + ")").css("display", "none");
                        $("#" + tablaId + " tfoot th:nth-child(" + columnIndex + ")").css("display", "none");
                    }
                });
            });
        ';
        }
        ?>
      }
    </script>

    </body>

    </html>
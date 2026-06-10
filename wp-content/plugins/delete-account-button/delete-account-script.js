(function($) {
  $(document).ready(function() {
      // Mostrar el dialogo de confirmacion
      function showConfirmationDialog(callback) {
          var dialog = $('#confirmation-dialog')[0];
          try {
            dialog.showModal();
        } catch (error) {
            console.error("Error al mostrar el modal:", error);
            console.error("Nombre del error:", error.name);
            console.error("Mensaje del error:", error.message);
            console.error("Stack del error:", error.stack);
        }

          $('#confirm-delete').off('click').on('click', function() {
              dialog.close();
              if (callback) callback(true);
          });

          $('#cancel-delete').off('click').on('click', function() {
              dialog.close();
              if (callback) callback(false);
          });
      }

      // Mostrar el dialogo de mensaje
      function showMessageDialog(message, isSuccess) {
          var dialog = $('#message-dialog')[0];
          $('#message-text').text(message);
          dialog.showModal();

          $('#close-message').off('click').on('click', function() {
              dialog.close();
              if (isSuccess) {
                  location.href = 'https://vacationintervalsmanagement.com/login/';
              }
          });
      }

      $(document).on('submit', '#delete-account-form', function(e) {
          e.preventDefault();
          var form = $(this);
          var url = form.attr('action');
          var data = form.serialize();

          // Mostrar el dialogo de confirmacio
          showConfirmationDialog(function(confirmed) {
              if (confirmed) {
                  $.ajax({
                      type: 'POST',
                      url: url,
                      data: data,
                      success: function(response) {
                          if (response.success) {
                              // Mostrar mensaje de exito
                              showMessageDialog('Your account has been deleted successfully.', true);
                          } else {
                              // Mostrar mensaje de error
                              showMessageDialog('Error: ' + response.data, false);
                          }
                      },
                      error: function(xhr, status, error) {
                          console.log('Status:', status);
                          console.log('Error:', error);
                          console.log('Response Text:', xhr.responseText);
                          // Mostrar mensaje de error
                          showMessageDialog('Something went wrong: ' + xhr.responseText, false);
                      }
                  });
              } else {
                  // Mostrar mensaje de cancelacion
                  showMessageDialog('Account deletion canceled.', false);
              }
          });
      });
  });
})(jQuery);






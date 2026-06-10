
document.addEventListener('DOMContentLoaded', function() {
    
    const eliminarBtns = document.querySelectorAll('.eliminar');
    let ajaxurl = '/wp-admin/admin-ajax.php';
    eliminarBtns.forEach(btn => {
        btn.addEventListener('click', function() {

            //mostrar un propmt para confirmar la eliminación
            if (!confirm('Are you sure? This action cannot be undone.')) {
                return;
            }

            //hacer el fetch usando POST a wp-admin/admin-ajax.php para elimiar la rental-submission por el id que está en data-id
            const data = new FormData();
            data.append('action', 'eliminar_rental_submission');
            data.append('post_id', btn.dataset.id);
            fetch(ajaxurl, {
                method: 'POST',
                body: data
            }).then(response => response.json()).then(data => {
                if (data.status === 'ok') {
                    //si la eliminación fue exitosa, remover la fila de la tabla
                    console.log(btn.dataset.id);
                    console.log(document.getElementById(btn.dataset.id));
                    document.getElementById(btn.dataset.id).remove();
                    btn.remove();
                    alert('Rental submission deleted successfully');
                    
                }
            }).catch(error => {
                console.log(error);
            });

        });
    });

});
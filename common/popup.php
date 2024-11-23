<?php
function mostraPopup($msg = '', $title = 'Messaggio')
{
    // Verifica se il messaggio è valorizzato
    ?>
    <!-- Modal HTML -->
    <div class="modal fade" id="msgModal" tabindex="-1" aria-labelledby="msgModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="msgModalLabel"><?= htmlspecialchars($title) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= htmlspecialchars($msg) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Mostra il modal quando la pagina è caricata
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('msgModal'));
            myModal.show();
        });
    </script>
    <?php
}
?>
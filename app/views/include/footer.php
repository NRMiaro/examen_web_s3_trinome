        </main>

        <footer class="footer">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>BNGRC</h4>
                    <p>Bureau National de Gestion des Risques et des Catastrophes.<br>
                    Suivi des collectes et distributions de dons pour les sinistrés.</p>
                </div>
                <div class="footer-col">
                    <h4>Contact</h4>
                    <p>
                        <i class="bi bi-envelope"></i> contact@bngrc.mg<br>
                        <i class="bi bi-telephone"></i> +261 20 22 123 45<br>
                        <i class="bi bi-geo-alt"></i> Antananarivo, Madagascar
                    </p>
                </div>
                <div class="footer-col">
                    <h4>Suivez-nous</h4>
                    <div class="footer-social">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-globe"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> BNGRC — ETU 4303, ETU 4044, ETU 3913
            </div>
        </footer>

    </div>
</div>

<div class="modal-overlay" id="modal-delete">
    <div class="modal">
        <div class="modal-header">
            <h3>Confirmer la suppression</h3>
            <button class="modal-close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body text-center">
            <div class="modal-icon danger">
                <i class="bi bi-exclamation-triangle" style="font-size: 28px;"></i>
            </div>
            <p>Êtes-vous sûr de vouloir supprimer cet élément ?<br>
            Cette action est <strong>irréversible</strong>.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" data-modal-close="modal-delete">Annuler</button>
            <button class="btn btn-danger">Supprimer</button>
        </div>
    </div>
</div>

<script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/dashboard.js"></script>
</body>
</html>

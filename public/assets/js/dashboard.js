
document.addEventListener('DOMContentLoaded', () => {

    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');

            const collapsed = document.body.classList.contains('sidebar-collapsed');
            try { localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0'); } catch(e) {}
        });

        try {
            if (localStorage.getItem('sidebar_collapsed') === '1'
                && window.innerWidth > 1024) {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch(e) {}
    }

    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    function openMobileSidebar() {
        document.body.classList.add('sidebar-mobile-open');
    }

    function closeMobileSidebar() {
        document.body.classList.remove('sidebar-mobile-open');
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            if (document.body.classList.contains('sidebar-mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeMobileSidebar);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
            closeAllModals();
        }
    });

    function openModal(modalId) {
        const overlay = document.getElementById(modalId);
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const overlay = document.getElementById(modalId);
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.modal-overlay.active').forEach(overlay => {
            overlay.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    window.openModal = openModal;
    window.closeModal = closeModal;

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const overlay = btn.closest('.modal-overlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-close');
            if (modalId) {
                closeModal(modalId);
            } else {
                const overlay = btn.closest('.modal-overlay');
                if (overlay) {
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    });

    document.querySelectorAll('[data-table-search]').forEach(input => {
        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);
        if (!table) return;

        input.addEventListener('input', () => {
            const query = input.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });

            updatePaginationInfo(tableId);
        });
    });

    document.querySelectorAll('.data-table thead th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const table = th.closest('.data-table');
            const tbody = table.querySelector('tbody');
            const colIndex = th.cellIndex;
            const isAsc = th.classList.contains('sorted-asc');

            table.querySelectorAll('thead th').forEach(h => {
                h.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
            });

            th.classList.add('sorted', isAsc ? 'sorted-desc' : 'sorted-asc');

            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                const aText = a.cells[colIndex]?.textContent.trim() || '';
                const bText = b.cells[colIndex]?.textContent.trim() || '';

                const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
                const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));

                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAsc ? bNum - aNum : aNum - bNum;
                }

                return isAsc
                    ? bText.localeCompare(aText, 'fr')
                    : aText.localeCompare(bText, 'fr');
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });

    const ROWS_PER_PAGE = 10;

    function initPagination(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / ROWS_PER_PAGE);

        if (totalPages <= 1) return; 

        let currentPage = 1;

        function showPage(page) {
            currentPage = page;
            const start = (page - 1) * ROWS_PER_PAGE;
            const end = start + ROWS_PER_PAGE;

            rows.forEach((row, i) => {

                if (row.style.display !== 'none' || !document.querySelector(`[data-table-search="${tableId}"]`)?.value) {
                    row.style.display = (i >= start && i < end) ? '' : 'none';
                }
            });

            updatePaginationInfo(tableId);
            renderPaginationButtons(tableId, page, totalPages);
        }

        showPage(1);
    }

    function renderPaginationButtons(tableId, currentPage, totalPages) {
        const container = document.querySelector(`[data-pagination="${tableId}"]`);
        if (!container) return;

        let html = '';

        html += `<button onclick="goToPage('${tableId}', ${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>&laquo;</button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<span class="page-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage('${tableId}', ${i})">${i}</span>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="page-btn" style="cursor:default;border:none">…</span>`;
            }
        }

        html += `<button onclick="goToPage('${tableId}', ${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>&raquo;</button>`;

        container.innerHTML = html;
    }

    window.goToPage = function(tableId, page) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const totalPages = Math.ceil(rows.length / ROWS_PER_PAGE);
        if (page < 1 || page > totalPages) return;

        const start = (page - 1) * ROWS_PER_PAGE;
        const end = start + ROWS_PER_PAGE;

        rows.forEach((row, i) => {
            row.style.display = (i >= start && i < end) ? '' : 'none';
        });

        updatePaginationInfo(tableId);
        renderPaginationButtons(tableId, page, totalPages);
    };

    function updatePaginationInfo(tableId) {
        const infoEl = document.querySelector(`[data-pagination-info="${tableId}"]`);
        if (!infoEl) return;

        const table = document.getElementById(tableId);
        const allRows = table.querySelectorAll('tbody tr');
        const visible = Array.from(allRows).filter(r => r.style.display !== 'none').length;

        infoEl.textContent = `${visible} sur ${allRows.length} entrées`;
    }

    document.querySelectorAll('.data-table[data-paginate]').forEach(table => {
        initPagination(table.id);
    });

    const stepperContainer = document.querySelector('.stepper');
    if (stepperContainer) {
        const steps = stepperContainer.querySelectorAll('.stepper-step');
        const lines = stepperContainer.querySelectorAll('.stepper-line');
        const contents = document.querySelectorAll('.stepper-content');
        const prevBtn = document.getElementById('stepper-prev');
        const nextBtn = document.getElementById('stepper-next');
        let currentStep = 0;

        function updateStepper() {
            steps.forEach((step, i) => {
                step.classList.remove('active', 'completed');
                if (i < currentStep) step.classList.add('completed');
                if (i === currentStep) step.classList.add('active');
            });

            lines.forEach((line, i) => {
                line.classList.toggle('completed', i < currentStep);
            });

            contents.forEach((content, i) => {
                content.classList.toggle('active', i === currentStep);
            });

            if (prevBtn) prevBtn.disabled = currentStep === 0;
            if (nextBtn) {
                if (currentStep === steps.length - 1) {
                    nextBtn.textContent = 'Confirmer';
                    nextBtn.classList.remove('btn-primary');
                    nextBtn.classList.add('btn-success');
                } else {
                    nextBtn.textContent = 'Suivant';
                    nextBtn.classList.remove('btn-success');
                    nextBtn.classList.add('btn-primary');
                }
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateStepper();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    updateStepper();
                }
            });
        }

        updateStepper();
    }

});

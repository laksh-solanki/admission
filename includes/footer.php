<?php
// ====================================================================
// Footer Layout Component
// This component closes HTML wrappers, renders the footer copyright,
// and imports JavaScript bundles (Bootstrap, custom scripts).
// ====================================================================
?>

    <!-- Bootstrap 5 Bundle with Popper JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebarCollapse = document.getElementById("sidebarCollapse");
            const sidebar = document.getElementById("sidebar");
            
            if (sidebarCollapse && sidebar) {
                sidebarCollapse.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }
        });
    </script>
</body>
</html>


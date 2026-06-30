    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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


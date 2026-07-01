<?php if (isset($is_public_page) && $is_public_page === true): ?>
<!-- Footer Section -->
<footer class="premium-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5 col-md-12">
                <h5 class="d-flex align-items-center">
                    <i class="fa-solid fa-graduation-cap me-2 text-info fs-4"></i>
                    <span>State College of Technology</span>
                </h5>
                <p class="mt-3 text-muted">A premier institution offering state-of-the-art technical education. Empowering students since 2002 to build the systems and innovations of tomorrow.</p>
                <div class="mt-4 d-flex gap-3">
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="text-dark">Quick Links</h5>
                <ul>
                    <li><a href="<?php echo app_base_path(); ?>portal.php">Portal Selection</a></li>
                    <li><a href="<?php echo app_base_path(); ?>index.php#steps">Admission Steps</a></li>
                    <li><a href="<?php echo app_base_path(); ?>courses.php">Academic Courses</a></li>
                    <li><a href="<?php echo app_base_path(); ?>student_register.php">Create Account</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <h5 class="text-dark">Help & Support</h5>
                <p class="small text-muted mb-2"><i class="fa-solid fa-envelope me-2 text-info"></i>admissions@statecollege.edu</p>
                <p class="small text-muted mb-2"><i class="fa-solid fa-phone me-2 text-info"></i>+1 (555) 019-2834</p>
                <p class="small text-muted"><i class="fa-solid fa-location-dot me-2 text-info"></i>100 Tech University Circle, Suite 400</p>
            </div>
        </div>
        
        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> State College of Technology. All Rights Reserved.</p>
        </div>
    </div>
</footer>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebarCollapse = document.getElementById("sidebarCollapse");
            const sidebar = document.getElementById("sidebar");
            
            if (sidebarCollapse && sidebar) {
                sidebarCollapse.addEventListener("click", function (e) {
                    sidebar.classList.toggle("active");
                    e.stopPropagation();
                });
                
                // Close sidebar when clicking outside on mobile devices
                document.addEventListener("click", function (e) {
                    if (window.innerWidth <= 768 && sidebar.classList.contains("active")) {
                        // Check if click was outside sidebar and outside toggle button
                        if (!sidebar.contains(e.target) && !sidebarCollapse.contains(e.target) && e.target !== sidebarCollapse) {
                            sidebar.classList.remove("active");
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>


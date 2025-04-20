    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CatatanKu - Aplikasi To Do List</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            body {
                background-color: #f5f5f5;
            }

            .hero {
                background: linear-gradient(135deg, #007bff, #00bfff);
                color: white;
                padding: 120px 0;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .hero::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,208C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
                background-size: cover;
                background-position: center;
                opacity: 0.3;
            }

            .logo {
                width: 80px;
                margin-bottom: 20px;
            }

            .hero h1 {
                font-size: 4em;
                margin-bottom: 20px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            }

            .hero p {
                font-size: 1.3em;
                margin-bottom: 40px;
                max-width: 700px;
                margin-left: auto;
                margin-right: auto;
                line-height: 1.6;
            }

            .cta-button {
                background-color: white;
                color: #007bff;
                padding: 18px 50px;
                border-radius: 50px;
                text-decoration: none;
                font-weight: bold;
                font-size: 1.2em;
                transition: all 0.3s ease;
                display: inline-block;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                border: none;
                cursor: pointer;
                z-index: 1;
                position: relative;
            }

            .cta-button:hover {
                background-color: #007bff;
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            }

            .features {
                padding: 100px 0;
                background: white;
                position: relative;
            }

            .features-container {
                max-width: 1300px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 40px;
                padding: 0 30px;
            }

            .feature-card {
                text-align: center;
                padding: 40px 30px;
                border-radius: 20px;
                background: #f8f9fa;
                position: relative;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                transition: transform 0.3s ease;
            }

            .feature-card:hover {
                transform: translateY(-10px);
            }

            .feature-icon {
                font-size: 3.5em;
                margin-bottom: 25px;
                color: #007bff;
            }

            .feature-card h3 {
                margin-bottom: 20px;
                color: #333;
                font-size: 1.5em;
            }

            .feature-card p {
                color: #666;
                line-height: 1.8;
                font-size: 1.1em;
            }

            .testimonials {
                padding: 100px 0;
                background: #f5f5f5;
                text-align: center;
                position: relative;
            }

            .testimonial-container {
                max-width: 1100px;
                margin: 0 auto;
                padding: 0 30px;
            }

            .testimonial-container h2 {
                font-size: 2.5em;
                margin-bottom: 50px;
                color: #333;
            }

            .testimonial-card {
                background: white;
                padding: 40px;
                border-radius: 20px;
                margin: 30px 0;
                box-shadow: 0 10px 30px rgba(0,0,0,0.08);
                transition: all 0.3s ease;
            }

            .testimonial-card:hover {
                transform: scale(1.02);
            }

            .testimonial-text {
                font-style: italic;
                color: #555;
                margin-bottom: 25px;
                font-size: 1.2em;
                line-height: 1.8;
            }

            .testimonial-author {
                font-weight: bold;
                color: #333;
                font-size: 1.1em;
            }

            .stats-section {
                padding: 80px 0;
                background: linear-gradient(135deg, #00bfff, #007bff);
                color: white;
                text-align: center;
            }

            .stats-container {
                max-width: 1200px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 30px;
                padding: 0 30px;
            }

            .stat-item {
                padding: 20px;
            }

            .stat-number {
                font-size: 3em;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .stat-label {
                font-size: 1.1em;
                opacity: 0.9;
            }

            .how-it-works {
                padding: 100px 0;
                background: white;
            }

            .how-it-works-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 30px;
            }

            .how-it-works h2 {
                text-align: center;
                font-size: 2.5em;
                margin-bottom: 50px;
                color: #333;
            }

            .steps {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 40px;
            }

            .step {
                text-align: center;
                padding: 30px;
            }

            .step-number {
                width: 50px;
                height: 50px;
                background: #007bff;
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.5em;
                margin: 0 auto 20px;
            }

            footer {
                background: #333;
                color: white;
                text-align: center;
                padding: 50px 0;
                font-size: 1.1em;
            }

            .footer-content {
                max-width: 1200px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 40px;
                padding: 0 30px;
                text-align: left;
            }

            .footer-section h3 {
                margin-bottom: 20px;
                font-size: 1.3em;
            }

            .footer-section ul {
                list-style: none;
            }

            .footer-section ul li {
                margin-bottom: 10px;
            }

            .footer-section ul li a {
                color: white;
                text-decoration: none;
                opacity: 0.8;
                transition: opacity 0.3s;
            }

            .footer-section ul li a:hover {
                opacity: 1;
            }

            .copyright {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid rgba(255,255,255,0.1);
            }
        </style>
    </head>
    <body>
        <section class="hero">
            <img src="img/logo/logo mesa.png" alt="CatatanKu Logo" class="logo">
            <h1>CatatanKu</h1>
            <p>Kelola tugas dan aktivitas Anda dengan mudah. Tingkatkan produktivitas dan capai tujuan Anda dengan CatatanKu.</p>
            <a href="login.php" class="cta-button">Mulai Sekarang</a>
        </section>

        <section class="features">
            <div class="features-container">
                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3>Mudah Digunakan</h3>
                    <p>Interface yang simpel dan intuitif membuat pengelolaan tugas menjadi lebih mudah dan menyenangkan.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Tetap Terorganisir</h3>
                    <p>Atur prioritas tugas, tetapkan tenggat waktu, dan kelola proyek dengan efisien.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Pantau Progres</h3>
                    <p>Lihat statistik dan perkembangan pencapaian tugas Anda secara real-time.</p>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Pengguna Aktif</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Tugas Selesai</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Kepuasan Pengguna</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Dukungan</div>
                </div>
            </div>
        </section>

        <section class="how-it-works">
            <div class="how-it-works-container">
                <h2>Cara Kerja CatatanKu</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Buat Akun</h3>
                        <p>Daftar dengan mudah menggunakan email Anda</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Tambah Tugas</h3>
                        <p>Buat dan atur tugas-tugas Anda</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Kelola & Selesaikan</h3>
                        <p>Pantau dan selesaikan tugas Anda</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="testimonials">
            <div class="testimonial-container">
                <h2>Apa Kata Pengguna Kami</h2>
                <div class="testimonial-card">
                    <p class="testimonial-text">"CatatanKu membantu saya mengatur waktu dengan lebih baik. Sekarang saya bisa fokus pada hal-hal yang benar-benar penting."</p>
                    <p class="testimonial-author">- Sarah Johnson</p>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Aplikasi yang sangat membantu untuk manajemen tugas sehari-hari. Sangat direkomendasikan!"</p>
                    <p class="testimonial-author">- Michael Chen</p>
                </div>
            </div>
        </section>

        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>CatatanKu</h3>
                    <p>Solusi terbaik untuk manajemen tugas dan produktivitas Anda.</p>
                </div>
                <div class="footer-section">
                    <h3>Tautan</h3>
                    <ul>
                        <li><a href="#">Beranda</a></li>
                        <li><a href="#">Fitur</a></li>
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Kontak</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Bantuan</h3>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Panduan</a></li>
                        <li><a href="#">Dukungan</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <ul>
                        <li><a href="#">Email</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 CatatanKu. All rights reserved.</p>
            </div>
        </footer>
    </body>
    </html>

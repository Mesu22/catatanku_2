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
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
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
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 3.5em;
            margin-bottom: 25px;
            color: #007bff;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.2);
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
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
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

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 30px 0;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <section class="hero">
        <img src="img/logo/logo mesa.png" alt="CatatanKu Logo" class="logo">
        <h1>CatatanKu</h1>
        <p>Kelola tugas dan aktivitas Anda dengan mudah. Tingkatkan produktivitas dan capai tujuan Anda dengan CatatanKu.</p>
        <a href="beranda.php" class="cta-button">Mulai Sekarang</a>
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
        <p>&copy; 2023 CatatanKu. All rights reserved.</p>
    </footer>
</body>
</html>

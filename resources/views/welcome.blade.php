<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>AgroSense — Smart Farming for a Sustainable Future</title>
<meta name="description" content="AgroSense connects your farm with real-time IoT data, AI insights, and smart irrigation control to increase yield and reduce waste.">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="/css/landing.css">
</head>
<body>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="nav">
  <a href="/" class="nav-logo">
    <div class="icon"><i class="fas fa-leaf"></i></div>
    <span>Agro<b>Sense</b></span>
  </a>
  <div class="nav-links">
    <a href="/" class="active">Home</a>
    <a href="#features">Features</a>
    <a href="{{ url('/dashboard') }}">Dashboard</a>
    <a href="#about">About Us</a>
    <a href="#contact">Contact</a>
  </div>
  <div class="nav-right">
    <button class="nav-theme" id="themeBtn"><i class="fas fa-moon"></i></button>
    @auth
      <a href="{{ url('/dashboard') }}" class="btn-cta"><i class="fas fa-th-large"></i> Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="btn-login">Login</a>
      <a href="{{ route('register') }}" class="btn-cta">Get Started</a>
    @endauth
  </div>
</nav>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-inner" style="max-width:1280px;margin:0 auto;width:100%">
    <div class="hero-left">
      <div class="badge"><span class="dot"></span> Smart Farming, Better Future</div>
      <h1 class="hero-title">Smart Farming<br>for a <span style="color:var(--g)">Sustainable</span><br>Future <span class="leaf">🌿</span></h1>
      <p class="hero-desc">AgroSense connects your farm with real-time IoT data, insights, and smart recommendations to help you increase yield and reduce waste.</p>
      @auth
        <a href="{{ url('/dashboard') }}" class="hero-btn"><i class="fas fa-th-large"></i> Go to Dashboard</a>
      @else
        <a href="{{ route('register') }}" class="hero-btn"><i class="fas fa-th-large"></i> Get Started Free</a>
      @endauth
    </div>

    <div class="hero-right">
      <div class="dash-card">
        <!-- Card Top Bar -->
        <div class="dc-top">
          <div class="dc-logo"><div class="icon"><i class="fas fa-leaf"></i></div> AgroSense</div>
          <div class="dc-actions">
            <i class="far fa-bell"></i><i class="far fa-comment-dots"></i>
            <div class="dc-avatar"></div> <span style="font-size:.78rem;font-weight:700;color:#1e293b">Farm Owner <i class="fas fa-chevron-down" style="font-size:.6rem;color:#94a3b8"></i></span>
          </div>
        </div>
        <!-- Sidebar + Main -->
        <div class="dc-body">
          <div class="dc-sidebar">
            <nav class="dc-nav">
              <a href="#" class="act"><i class="fas fa-th-large"></i> Dashboard</a>
              <a href="#"><i class="fas fa-map-marked-alt"></i> Farm Overview</a>
              <a href="#"><i class="fas fa-microchip"></i> Sensors</a>
              <a href="#"><i class="fas fa-tint"></i> Irrigation</a>
              <a href="#"><i class="far fa-bell"></i> Alerts</a>
              <a href="#"><i class="fas fa-chart-line"></i> Analytics</a>
              <a href="#"><i class="far fa-file-alt"></i> Reports</a>
              <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </nav>
          </div>
          <div class="dc-main">
            <div class="dc-title">Dashboard</div>
            <div class="dc-stats">
              <div class="dc-stat"><div class="label">Soil Moisture</div><div class="val" style="color:#0ea5e9">45%</div><div class="status">Optimal</div></div>
              <div class="dc-stat"><div class="label">Temperature</div><div class="val" style="color:#f97316">30.2°C</div><div class="status" style="color:#3b82f6">Normal</div></div>
              <div class="dc-stat"><div class="label">Humidity</div><div class="val" style="color:#06b6d4">70%</div><div class="status">Optimal</div></div>
              <div class="dc-stat"><div class="label">Soil pH</div><div class="val">6.5</div><div class="status">Optimal</div></div>
            </div>
            <div class="dc-charts">
              <div class="dc-chart-box">
                <h4>Soil Moisture (7 Days)</h4>
                <svg viewBox="0 0 280 70" preserveAspectRatio="none">
                  <defs><linearGradient id="sg" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#1a7a40" stop-opacity=".15"/><stop offset="100%" stop-color="#1a7a40" stop-opacity="0"/></linearGradient></defs>
                  <path d="M0 50 L40 38 L80 42 L120 30 L160 38 L200 44 L240 26 L280 32 L280 70 L0 70Z" fill="url(#sg)"/>
                  <path d="M0 50 L40 38 L80 42 L120 30 L160 38 L200 44 L240 26 L280 32" fill="none" stroke="#1a7a40" stroke-width="2" stroke-linejoin="round"/>
                  <circle cx="240" cy="26" r="4" fill="#1a7a40" stroke="#fff" stroke-width="1.5"/>
                  <rect x="210" y="12" width="36" height="14" rx="4" fill="#1a7a40"/>
                  <text x="228" y="22" font-size="8" fill="#fff" text-anchor="middle" font-weight="700">45%</text>
                  <text x="0" y="68" font-size="7" fill="#94a3b8">May 10</text>
                  <text x="50" y="68" font-size="7" fill="#94a3b8">May 12</text>
                  <text x="110" y="68" font-size="7" fill="#94a3b8">May 14</text>
                  <text x="170" y="68" font-size="7" fill="#94a3b8">May 16</text>
                  <text x="240" y="68" font-size="7" fill="#94a3b8">May 18</text>
                </svg>
              </div>
              <div class="dc-donut-box">
                <h4>Irrigation</h4>
                <div class="donut-ring"><span class="donut-pct">60%</span></div>
                <div style="font-size:.65rem;color:#64748b;margin:.3rem 0">Water Tank</div>
                <div class="pump-badge">Pump: ON</div>
                <div style="font-size:.62rem;color:#94a3b8;margin-top:.3rem">Last run: 2 hrs ago</div>
              </div>
            </div>
            <div class="dc-alerts">
              <h4>Recent Alerts <a href="#">View all</a></h4>
              <div class="alert-item"><i class="fas fa-exclamation-triangle" style="color:#f97316"></i><div><div style="font-weight:700;color:#1e293b">Soil moisture low in Field 2</div><div style="color:#94a3b8">Consider irrigating the field</div></div><span style="color:#94a3b8;white-space:nowrap">10 min ago</span></div>
              <div class="alert-item"><i class="fas fa-info-circle" style="color:#3b82f6"></i><div><div style="font-weight:700;color:#1e293b">Light rain expected tomorrow</div></div><span style="color:#94a3b8;white-space:nowrap">2 hrs ago</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════ FEATURE STRIP ═══════════════ -->
<section class="features-strip" id="features">
  <div class="strip-grid reveal">
    <div class="strip-card"><div class="strip-icon sc-green"><i class="fas fa-broadcast-tower"></i></div><div><h3>Real-time Monitoring</h3><p>Monitor soil, weather and environment in real-time</p></div></div>
    <div class="strip-card"><div class="strip-icon sc-lime"><i class="fas fa-seedling"></i></div><div><h3>Smart Insights</h3><p>Get AI-powered insights and actionable recommendations</p></div></div>
    <div class="strip-card"><div class="strip-icon sc-sky"><i class="fas fa-tint"></i></div><div><h3>Water Management</h3><p>Optimize irrigation and conserve water</p></div></div>
    <div class="strip-card"><div class="strip-icon sc-teal"><i class="fas fa-shield-alt"></i></div><div><h3>Increase Productivity</h3><p>Improve crop yield and maximize your profits</p></div></div>
  </div>
</section>

<!-- ═══════════════ WHY AGROSENSE ═══════════════ -->
<section class="why" id="about">
  <div class="why-left reveal">
    <div class="why-tag">Why AgroSense?</div>
    <h2>Technology that cares for your farm</h2>
    <div class="why-divider"></div>
  </div>
  <div class="why-grid reveal">
    <div class="why-item"><div class="wicon"><i class="fas fa-wifi"></i></div><h4>IoT Sensor Integration</h4><p>Seamlessly connects with field sensors to collect accurate data with sub-second latency.</p></div>
    <div class="why-item"><div class="wicon"><i class="far fa-bell"></i></div><h4>Real-time Alerts</h4><p>Get instant SMS and push notifications about critical changes in your farm conditions.</p></div>
    <div class="why-item"><div class="wicon"><i class="fas fa-chart-bar"></i></div><h4>Data-driven Decisions</h4><p>Visualize historical trends and machine learning insights for smarter farming.</p></div>
  </div>
</section>

<!-- ═══════════════ HOW IT WORKS ═══════════════ -->
<section class="how">
  <div class="how-head reveal">
    <div class="tag">How It Works</div>
    <h2>From sensors to harvest — automated</h2>
    <p>Four simple steps to transform your farm into a smart operation</p>
  </div>
  <div class="how-steps reveal">
    <div class="step"><div class="step-num"><i class="fas fa-microchip"></i></div><h3>Install Sensors</h3><p>Place IoT sensors in your fields to track soil, temperature, and humidity 24/7.</p></div>
    <div class="step"><div class="step-num"><i class="fas fa-cloud-upload-alt"></i></div><h3>Connect to Cloud</h3><p>Sensor data streams securely to AgroSense's cloud platform in real-time via MQTT.</p></div>
    <div class="step"><div class="step-num"><i class="fas fa-brain"></i></div><h3>AI Analysis</h3><p>Our models analyze patterns and generate actionable crop management recommendations.</p></div>
    <div class="step"><div class="step-num"><i class="fas fa-tractor"></i></div><h3>Act &amp; Grow</h3><p>Automate irrigation, get disease alerts, and watch your yield increase season after season.</p></div>
  </div>
</section>

<!-- ═══════════════ STATS ═══════════════ -->
<section class="stats reveal">
  <div class="stats-grid">
    <div class="stat-box"><div class="num">10k<span class="unit">+</span></div><p>Acres Monitored</p></div>
    <div class="stat-box"><div class="num">50M<span class="unit">+</span></div><p>Data Points / Day</p></div>
    <div class="stat-box"><div class="num">94<span class="unit">%</span></div><p>AI Accuracy Rate</p></div>
    <div class="stat-box"><div class="num">30<span class="unit">%</span></div><p>Water Saved Avg.</p></div>
  </div>
</section>

<!-- ═══════════════ TESTIMONIALS ═══════════════ -->
<section class="testi">
  <div class="section-head reveal">
    <div class="tag">Testimonials</div>
    <h2>Trusted by farmers worldwide</h2>
  </div>
  <div class="testi-grid reveal">
    <div class="testi-card"><div class="stars">★★★★★</div><p>"AgroSense cut our water usage by 28% in the first season. The real-time alerts saved our wheat crop from a sudden blight before it spread."</p><div class="testi-author"><div class="author-avatar">RK</div><div class="author-info"><strong>Rajesh Kumar</strong><span>Wheat Farmer, Punjab</span></div></div></div>
    <div class="testi-card"><div class="stars">★★★★★</div><p>"The dashboard is incredibly intuitive. I can monitor all 5 of my fields from my phone while I'm at the market. Game changer."</p><div class="testi-author"><div class="author-avatar">SA</div><div class="author-info"><strong>Sara Ahmad</strong><span>Vegetable Grower, Sindh</span></div></div></div>
    <div class="testi-card"><div class="stars">★★★★★</div><p>"I was skeptical about smart farming, but AgroSense's AI recommendations increased my rice yield by 22% compared to last year."</p><div class="testi-author"><div class="author-avatar">MP</div><div class="author-info"><strong>Michael Patel</strong><span>Rice Farmer, Gujarat</span></div></div></div>
  </div>
</section>

<!-- ═══════════════ CTA BANNER ═══════════════ -->
<div class="cta-banner reveal">
  <div>
    <h2>Ready to grow smarter?</h2>
    <p>Join thousands of farmers already using AgroSense to transform their operations and boost profitability.</p>
  </div>
  @auth
    <a href="{{ url('/dashboard') }}" class="btn-cta-white">Go to Dashboard →</a>
  @else
    <a href="{{ route('register') }}" class="btn-cta-white">Start Free Today →</a>
  @endauth
</div>

<!-- ═══════════════ FOOTER ═══════════════ -->
<footer id="contact">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="fl"><div class="icon"><i class="fas fa-leaf"></i></div> AgroSense</div>
      <p>Empowering modern agriculture with cutting-edge IoT and AI technology to build a sustainable future for every farm.</p>
      <div class="socials">
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-facebook-f"></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Product</h4>
      <ul>
        <li><a href="#features">Features</a></li>
        <li><a href="#">Pricing</a></li>
        <li><a href="#">Hardware</a></li>
        <li><a href="#">Case Studies</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="#about">About Us</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Cookie Policy</a></li>
        <li><a href="#">Security</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bar">
    <span>© {{ date('Y') }} AgroSense Technologies. All rights reserved.</span>
    <span>Designed for modern farmers 🌱</span>
  </div>
</footer>

<script>
// Scroll reveal
const observer=new IntersectionObserver(els=>{els.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible')}})},{threshold:.15});
document.querySelectorAll('.reveal').forEach(el=>observer.observe(el));

// Theme toggle
document.getElementById('themeBtn').addEventListener('click',function(){
  const i=this.querySelector('i');
  i.classList.toggle('fa-moon'); i.classList.toggle('fa-sun');
  document.body.classList.toggle('dark');
});
</script>
</body>
</html>

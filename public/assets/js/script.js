
console.log('hello')
// Changing the defaults
window.sr = ScrollReveal({ reset: true });

// Customizing a reveal set
sr.reveal('.categorie_cart', { interval: 200, scale: 0.2, reset: true });
sr.reveal('.about_categorie', { interval: 600, scale: 0.2 });

// menu burger
var navLinks = document.getElementById("nav-links");
var btnMenu = document.getElementById("hamburger");
var body = document.body
// for (var i = 0; i < btnMenu.length; i++) {

    
    btnMenu.addEventListener('click', function () {
        console.log(btnMenu)
        btnMenu.classList.toggle('is-active');
        navLinks.classList.toggle('open')
    });
// 
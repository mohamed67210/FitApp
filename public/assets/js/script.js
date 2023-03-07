
console.log('hello')
// changer couleur de la nav apres scroll
var navbar = document.querySelector('nav');
window.onscroll = function(){
    if (window.scrollY > 20) {
        navbar.classList.add('nav-active');
    } else {
        navbar.classList.remove('nav-active');
    }
}

// animation after scroll (pour categories et section about)
// Changing the defaults
window.sr = ScrollReveal({ reset: true });
// Customizing a reveal set
sr.reveal('#about_section', { scale: 0.5});
sr.reveal('.categorie_cart', { interval: 200, scale: 0.2, reset: true });
sr.reveal('.about_categorie', { interval: 600, scale: 0.2 });

// menu burger
var navLinks = document.getElementById("nav-links");
var btnMenu = document.getElementById("hamburger");
var body = document.body

btnMenu.addEventListener('click', function () {
    console.log(btnMenu)
    btnMenu.classList.toggle('is-active');
    navLinks.classList.toggle('open')
});

// pour le bouton au milieu du nav
var userBtn = document.getElementById('menu_user')
var userBtnLinks = document.getElementById('user_links')
console.log(userBtnLinks)
userBtn.addEventListener('click',function(){
    userBtnLinks.classList.toggle('open')
})

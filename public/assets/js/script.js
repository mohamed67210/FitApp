console.log('hello')
// changer couleur de la nav apres scroll
var navbar = document.querySelector('nav');
window.onscroll = function () {
    if (window.scrollY > 20) {
        navbar.classList.add('nav-active');
    } else {
        navbar.classList.remove('nav-active');
    }
}
// menu burger
var navLinks = document.getElementById("nav-links");
var btnMenu = document.getElementById("hamburger");
var body = document.body
btnMenu.addEventListener('click', function () {
    btnMenu.classList.toggle('is-active');
    navLinks.classList.toggle('open')
});
// animation after scroll (pour categories et section about)
// Changing the defaults
window.sr = ScrollReveal({ reset: true });
// Customizing a reveal set
sr.reveal('#about_section', { scale: 0.5 });
sr.reveal('.categorie_cart', { interval: 200, scale: 0.2, reset: true });
sr.reveal('.about_categorie', { interval: 600, scale: 0.2 });


// nouveauté slider 
var currentSlide = 0;
const prevButton = document.querySelector('.fa-chevron-left');
const nextButton = document.querySelector('.fa-chevron-right');
if (prevButton != null) {

    prevButton.addEventListener('click', function () {
        currentSlide--;
        showSlide();
    })
    nextButton.addEventListener('click', function () {
        currentSlide++;
        showSlide();
    })
    setInterval(showSlide, 0);
}

function showSlide() {
    const slides = document.querySelectorAll('.slide');
    // si on arrive a la fin du slide on retourne au debut
    if (currentSlide < 0) {
        currentSlide = slides.length - 1;
    }
    else if (currentSlide >= slides.length) {
        currentSlide = 0;
    }
    // masquer tous les slides et afficher seulement celui actuel
    slides.forEach(slide => slide.style.display = 'none');
    slides[currentSlide].style.display = "flex";
    // console.log(slides[currentSlide])
}





// afficher cacher formulaire ajout diplome das page profile
const btn = document.getElementById('diplome_form_btn')
const form = document.getElementById('diplome_form')
if(btn != null){
    btn.addEventListener('click', function () {
        console.log('yesyesy')
        form.classList.toggle('open')
    })
}


// page membre bouton tous les cours /liste de souhaits
const modules_link = document.querySelector('.modules_link')
const favorie_link = document.querySelector('.favories_link')
const favorie_items = document.querySelector('.favories_container')
if (modules_link != null) {
    modules_link.addEventListener('click',function(){
        favorie_items.style.display = "none"
    })
    favorie_link.addEventListener('click', function () {
        favorie_items.style.display = "flex"
    })
}


// menu coach ou admin au milieu de la navbar
const menuUser = document.getElementById('menu_user');
const menuUserLinks = document.getElementById('user_links');
if(menuUser != null){
    menuUser.addEventListener('click', function () {
        menuUserLinks.classList.toggle('open')
    })
}
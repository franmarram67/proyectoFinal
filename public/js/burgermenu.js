const menuBtn = document.querySelector('.menu-btn');
const headerNav = document.querySelector('header nav');
let menuOpen = false;
menuBtn.addEventListener('click',() => {
    if(!menuOpen) {
        menuBtn.classList.add('open');
        headerNav.classList.add('open');
        menuOpen = true;
    } else {
        menuBtn.classList.remove('open');
        headerNav.classList.remove('open');
        menuOpen = false;
    }
});

// window.addEventListener('resize',()=>{
//     if(window.innerWidth>768) {
//         menuBtn.classList.remove('open');
//         headerNav.classList.remove('open');
//         menuOpen = false;
//     }
// });
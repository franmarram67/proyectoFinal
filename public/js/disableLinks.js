var links=document.querySelectorAll(".disabled");
for(let i=0;i<links.length;i++) {
    links[i].addEventListener('click',function(e){
        e.preventDefault();
    });
}

console.log("XD");
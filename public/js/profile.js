$(document).ready(function() {
    $(".hideshow a").siblings().hide();

    $(".hideshow.email a").on("click",function(){
        hideShow($(this),"Change Email");
    });

    $(".hideshow.password a").on("click",function(){
        hideShow($(this),"Change Password");
    });

    $(".hideshow.picture a").on("click",function(){
        hideShow($(this),"Change Picture");
    });

    $(".hideshow.dni a").on("click",function(){
        hideShow($(this),"Change DNI");
    });

    $(".hideshow.name a").on("click",function(){
        hideShow($(this),"Change Name");
    });

    $(".hideshow.surname a").on("click",function(){
        hideShow($(this),"Change Surname");
    });

    $(".hideshow.province a").on("click",function(){
        hideShow($(this),"Change Province");
    });

    function hideShow(obj,showValue) {
        console.log(obj.siblings("div").is(":hidden"));
        if(obj.siblings("div").eq(0).is(":hidden")) {
            obj.siblings("div").eq(0).show();
            obj.text("Hide");
        } else {
            obj.siblings("div").eq(0).hide();
            obj.text(showValue);
        }
    }

});


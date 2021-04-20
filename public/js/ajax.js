$(document).ready(function() {

    function finishTournament(id1,id2,id3,id4,idT) {
        $.ajax({
            type: "GET",
            url: "/finishtournamentajax/"+idT,
            data: { 
                firstPlace: id1,
                secondPlace: id2,
                thirdPlace: id3,
                fourthPlace: id4,
            },
    
            // ------v-------use it as the callback function
            success: function(data) {
                //console.log(data);
                window.location.replace("/seetournament/"+idT);
            },
            error: function(request, error) {
                console.log(request, error);
            }
        });
    }

    $(".fakelink").mousedown(function(){
        $(this).css("color","red");
    });

    $(".fakelink").mouseup(function(){
        $(this).css("color","purple");
    });
    
    $(".finishtournament").on("click",function(){
        var id1=$("#firstPlace").val();
        var id2=$("#secondPlace").val();
        var id3=$("#thirdPlace").val();
        var id4=$("#fourthPlace").val();
        var idT=$("#tournamentId").val();

        console.log("firstPlace: "+id1);
        console.log("secondPlace: "+id2);
        console.log("thirdPlace: "+id3);
        console.log("fourthPlace: "+id4);
        console.log("tournamentId: "+idT);
        console.log("----")

        if(id1!=id2&&id1!=id3&&id1!=id4 && id2!=id3&&id2!=id4 && id3!=id4) {
            finishTournament(id1,id2,id3,id4,idT);
        } else {
            window.alert("The same user can't be in two or more different places at the same time.")
        }
        
    });

    function deleteTournament(idT) {
        $.ajax({
            type: "GET",
            url: "/deletetournamentajax/"+idT,
            data: { 

            },
    
            // ------v-------use it as the callback function
            success: function(data) {
                //console.log(data);
                window.location.replace("/");
            },
            error: function(request, error) {
                console.log(request, error);
            }
        });
    }

    $(".deletetournament").on("click",function(){
        var idT=$("#tournamentId").val();
        console.log("tournamentId: "+idT);
        console.log("----");
        var c = confirm("Are you sure you want to delete this Tournament?");4
        if(c) {
            deleteTournament(idT);
        }
        
    });

    

});
$("#searchRanking").on("click",function(){
    let provinceId = $("#province").val();
    let videogameId = $("#videogame").val();
    let year = $("#year").val();
    console.log("Province Id:",provinceId);
    console.log("VideoGame Id:",videogameId);
    console.log("Year:",year);
    window.location.replace("/ranking/"+provinceId+"&"+videogameId+"&"+year);
});
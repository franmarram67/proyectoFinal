SELECT * FROM points, user WHERE points.user_id = user.id;
SELECT SUM(points.amount) FROM points, user WHERE points.user_id = 2;

SELECT user_id, SUM(amount) total_puntos FROM points GROUP BY user_id;
SELECT user.id, SUM(points.amount) total_puntos FROM points, user WHERE points.user_id = user.id GROUP BY 1;
SELECT user.id, SUM(points.amount) total_puntos FROM points JOIN user ON (points.user_id = user.id) GROUP BY 1; -- ESTAS FUNCIONAN, hay que cambiar group by 1 por group by user.id

SELECT user.id, SUM(points.amount) total_puntos FROM points, user, tournament, province, video_game WHERE points.user_id = user.id AND user.province_id = 1 AND year(points.datetime) = 2021 AND points.tournament_id = tournament.id AND tournament.videogame_id = 1 GROUP BY user.id; --ESTA DA DEMASIADOS PUNTOS

SELECT video_game.title, YEAR(finish_date) date, province.name, user.name,SUM(points.amount) puntos
FROM points JOIN user ON points.user_id = user.id
   JOIN province ON province.id = user.province_id
   JOIN tournament_user ON tournament_user.user_id = user.id
   JOIN tournament ON tournament_user.tournament_id = tournament.id
   JOIN video_game ON tournament.videogame_id = video_game.id
GROUP BY video_game.title,date,province.name,user.name
ORDER BY puntos DESC;

SELECT user.email, SUM(points.amount) total_puntos 
FROM user, points 
WHERE points.user_id = user.id 
AND year(points.datetime) = 2021 
GROUP BY user.email
ORDER BY total_puntos DESC; --Parte de la fecha va bien. Queda la provincia y el videojuego

SELECT user.email, SUM(points.amount) total_puntos 
FROM user, points
WHERE points.user_id = user.id 
AND year(points.datetime) = 2021 
AND user.province_id = 2 
GROUP BY user.email 
ORDER BY total_puntos DESC; --Esta da los resultados buenos. Parte de fecha y provincia da resultado. Falta la parte del videojuego

SELECT user.email, SUM(points.amount) total_puntos 
FROM user, points, tournament 
JOIN points p JOIN tournament t ON p.tournament_id = t.id 
WHERE points.user_id = user.id 
AND year(points.datetime) = 2021 
AND user.province_id = 2 
AND t.videogame_id = 1
GROUP BY user.email 
ORDER BY total_puntos DESC; --Prueba de conseguir el torneo fallida porque salen demasiados puntos

SELECT user.email, SUM(points.amount) total_puntos 
FROM user, points, tournament 
WHERE points.user_id = user.id 
AND year(points.datetime) = 2021 
AND user.province_id = 2 
AND points.tournament_id = tournament.id 
AND tournament.videogame_id = 1 
GROUP BY user.email
ORDER BY total_puntos DESC; --Parece que esta si da todo lo que tiene que dar

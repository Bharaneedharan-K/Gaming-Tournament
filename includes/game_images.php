<?php
function getGameImage($gameName) {
    $gameImages = [
        'Among Us' => 'among-us.jpg',
        'PUBG' => 'pubg.jpg',
        'Fortnite' => 'fortnite.jpg',
        'Call of Duty' => 'cod.jpg',
        'Minecraft' => 'minecraft.jpg',
        'League of Legends' => 'lol.jpg',
        'Valorant' => 'valorant.jpg',
        'CS:GO' => 'csgo.jpg',
        'Dota 2' => 'dota2.jpg',
        'FIFA' => 'fifa.jpg',
        'GTA V' => 'gtav.jpg',
        'Rocket League' => 'rocket-league.jpg',
        'Apex Legends' => 'apex-legends.jpg'
    ];
    
    // Default image if game not found in mapping
    $defaultImage = 'default-game.jpg';
    
    return isset($gameImages[$gameName]) ? $gameImages[$gameName] : $defaultImage;
}

function getGameImagePath($gameName) {
    return 'assets/images/games/' . getGameImage($gameName);
}
?> 
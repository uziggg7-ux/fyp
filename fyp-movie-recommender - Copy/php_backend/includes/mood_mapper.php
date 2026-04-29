<?php
/**
 * mood_mapper.php - Centralized Mood-to-Genre Mapping Protocol
 *
 * Fetches mood-to-genre mappings from the database.
 */

require_once __DIR__ . '/../database/connection.php';

if (!function_exists('get_genre_id_for_mood')) {
    function get_genre_id_for_mood($mood) {
        global $pdo;

        try {
            $stmt = $pdo->prepare("SELECT associated_genres FROM moods WHERE name = ? LIMIT 1");
            $stmt->execute([ucfirst(strtolower($mood))]);
            $genre_ids = $stmt->fetchColumn();

            if ($genre_ids) {
                // If multiple genres are mapped, pick the first one or handle appropriately
                $ids = explode(',', $genre_ids);
                return (int)trim($ids[0]);
            }
        } catch (Exception $e) {
            error_log("Database error in mood_mapper: " . $e->getMessage());
        }

        // Local fallback if DB fails or mood not found
        $fallback_map = [
            'Happy'    => 35,
            'Sad'      => 18,
            'Angry'    => 28,
            'Excited'  => 10751,
            'Anxious'  => 53,
            'Relaxed'  => 10749,
            'Neutral'  => 10752,
            'Default'  => 35
        ];

        $mood_key = ucfirst(strtolower($mood));
        return $fallback_map[$mood_key] ?? $fallback_map['Default'];
    }
}
?>

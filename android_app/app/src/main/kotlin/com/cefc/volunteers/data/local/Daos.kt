package com.cefc.volunteers.data.local

import androidx.room.*
import kotlinx.coroutines.flow.Flow

@Dao
interface SermonDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertSermon(sermon: SermonEntity)

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertSermons(sermons: List<SermonEntity>)

    @Update
    suspend fun updateSermon(sermon: SermonEntity)

    @Delete
    suspend fun deleteSermon(sermon: SermonEntity)

    @Query("DELETE FROM sermons WHERE id = :id")
    suspend fun deleteSermonById(id: Int)

    @Query("SELECT * FROM sermons ORDER BY date DESC")
    fun getAllSermons(): Flow<List<SermonEntity>>

    @Query("SELECT * FROM sermons WHERE id = :id")
    fun getSermonById(id: Int): Flow<SermonEntity?>

    @Query("SELECT * FROM sermons WHERE ministry = :ministry ORDER BY date DESC")
    fun getSermonsByMinistry(ministry: String): Flow<List<SermonEntity>>

    @Query("SELECT * FROM sermons WHERE isFavorite = 1 ORDER BY date DESC")
    fun getFavoriteSermons(): Flow<List<SermonEntity>>

    @Query("SELECT * FROM sermons WHERE isDownloaded = 1 ORDER BY date DESC")
    fun getDownloadedSermons(): Flow<List<SermonEntity>>

    @Query("SELECT * FROM sermons WHERE title LIKE '%' || :query || '%' OR speaker LIKE '%' || :query || '%' ORDER BY date DESC")
    fun searchSermons(query: String): Flow<List<SermonEntity>>

    @Query("UPDATE sermons SET isFavorite = :isFavorite WHERE id = :id")
    suspend fun toggleFavorite(id: Int, isFavorite: Boolean)

    @Query("UPDATE sermons SET isDownloaded = :isDownloaded, localPath = :localPath WHERE id = :id")
    suspend fun updateDownloadStatus(id: Int, isDownloaded: Boolean, localPath: String?)

    @Query("DELETE FROM sermons")
    suspend fun deleteAllSermons()

    @Query("SELECT COUNT(*) FROM sermons")
    fun getSermonCount(): Flow<Int>
}

@Dao
interface UserDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertUser(user: UserEntity)

    @Update
    suspend fun updateUser(user: UserEntity)

    @Delete
    suspend fun deleteUser(user: UserEntity)

    @Query("SELECT * FROM users WHERE id = :id")
    fun getUserById(id: Int): Flow<UserEntity?>

    @Query("SELECT * FROM users WHERE isLoggedIn = 1 LIMIT 1")
    fun getCurrentUser(): Flow<UserEntity?>

    @Query("SELECT * FROM users")
    fun getAllUsers(): Flow<List<UserEntity>>

    @Query("UPDATE users SET isLoggedIn = 0 WHERE id = :id")
    suspend fun logoutUser(id: Int)

    @Query("DELETE FROM users WHERE isLoggedIn = 0")
    suspend fun clearLoggedOutUsers()

    @Query("DELETE FROM users")
    suspend fun deleteAllUsers()
}

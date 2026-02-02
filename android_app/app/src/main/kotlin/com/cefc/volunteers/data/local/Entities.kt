package com.cefc.volunteers.data.local

import androidx.room.Entity
import androidx.room.PrimaryKey
import androidx.room.TypeConverter
import com.google.gson.Gson

@Entity(tableName = "sermons")
data class SermonEntity(
    @PrimaryKey
    val id: Int,
    val title: String,
    val speaker: String,
    val date: String,
    val ministry: String? = null,
    val description: String? = null,
    val thumbnail: String? = null,
    val fileUrl: String? = null,
    val isDownloaded: Boolean = false,
    val localPath: String? = null,
    val isFavorite: Boolean = false,
    val syncedAt: Long = System.currentTimeMillis()
)

@Entity(tableName = "users")
data class UserEntity(
    @PrimaryKey
    val id: Int,
    val name: String,
    val email: String,
    val phone: String,
    val ministry: String,
    val role: String,
    val joinDate: String,
    val avatar: String,
    val lastLogin: Long,
    val isLoggedIn: Boolean = true,
    val loginTime: Long = System.currentTimeMillis()
)

class Converters {
    private val gson = Gson()

    @TypeConverter
    fun fromString(value: String?): Map<String, Any>? {
        return if (value == null) null else gson.fromJson(value, Map::class.java) as Map<String, Any>
    }

    @TypeConverter
    fun mapToString(map: Map<String, Any>?): String? {
        return if (map == null) null else gson.toJson(map)
    }
}

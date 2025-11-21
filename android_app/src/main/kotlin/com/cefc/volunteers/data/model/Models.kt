package com.cefc.volunteers.data.model

import com.google.gson.annotations.SerializedName
import java.io.Serializable

// Base Response
data class BaseResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String
)

// Login Response
data class LoginResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String,
    @SerializedName("user")
    val user: User? = null
)

data class User(
    @SerializedName("id")
    val id: Int,
    @SerializedName("name")
    val name: String,
    @SerializedName("email")
    val email: String,
    @SerializedName("phone")
    val phone: String,
    @SerializedName("ministry")
    val ministry: String,
    @SerializedName("role")
    val role: String,
    @SerializedName("join_date")
    val joinDate: String,
    @SerializedName("avatar")
    val avatar: String,
    @SerializedName("last_login")
    val lastLogin: Long
) : Serializable

// Signup Response
data class SignupResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String
)

// Verify Response
data class VerifyResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String
)

// Sermons Response
data class SermonsResponse(
    @SerializedName("sermons")
    val sermons: List<Sermon>,
    @SerializedName("success")
    val success: Boolean = true,
    @SerializedName("message")
    val message: String = ""
)

data class Sermon(
    @SerializedName("id")
    val id: Int,
    @SerializedName("title")
    val title: String,
    @SerializedName("speaker")
    val speaker: String,
    @SerializedName("date")
    val date: String,
    @SerializedName("ministry")
    val ministry: String? = null,
    @SerializedName("description")
    val description: String? = null,
    @SerializedName("content")
    val content: String? = null,
    @SerializedName("thumbnail")
    val thumbnail: String? = null,
    @SerializedName("youtube")
    val youtube: String? = null,
    @SerializedName("file_url")
    val fileUrl: String? = null,
    @SerializedName("duration")
    val duration: String? = null,
    @SerializedName("views")
    val views: Int = 0,
    @SerializedName("uploaded_by")
    val uploadedBy: String? = null,
    @SerializedName("created_at")
    val createdAt: String? = null
) : Serializable

// Sermon Detail Response
data class SermonDetailResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("sermon")
    val sermon: Sermon? = null,
    @SerializedName("message")
    val message: String = ""
)

// Upload Response
data class UploadResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String,
    @SerializedName("file_url")
    val fileUrl: String? = null,
    @SerializedName("id")
    val id: Int? = null
)

// Sermon Upload Request
data class SermonUploadRequest(
    val title: String,
    val speaker: String,
    val ministry: String,
    val date: String,
    val description: String,
    val filePath: String,
    val thumbnailPath: String? = null
) : Serializable

// Pagination
data class PaginatedResponse<T>(
    @SerializedName("data")
    val data: List<T>,
    @SerializedName("total")
    val total: Int,
    @SerializedName("page")
    val page: Int,
    @SerializedName("per_page")
    val perPage: Int
)

// Error Response
data class ErrorResponse(
    @SerializedName("success")
    val success: Boolean = false,
    @SerializedName("message")
    val message: String,
    @SerializedName("error_code")
    val errorCode: String? = null
)

// Local Database Models
data class LocalSermon(
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

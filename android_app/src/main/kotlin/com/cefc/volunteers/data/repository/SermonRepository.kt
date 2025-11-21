package com.cefc.volunteers.data.repository

import com.cefc.volunteers.data.api.ApiService
import com.cefc.volunteers.data.local.AppDatabase
import com.cefc.volunteers.data.local.SermonEntity
import com.cefc.volunteers.data.model.Sermon
import com.cefc.volunteers.data.model.SermonsResponse
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File

class SermonRepository(
    private val apiService: ApiService,
    private val database: AppDatabase,
    private val securityManager: SecurityManager
) {

    fun getSermons(): Flow<Result<SermonsResponse>> = flow {
        try {
            val response = apiService.getSermons()
            if (response.isSuccessful && response.body() != null) {
                val sermons = response.body()!!.sermons
                // Cache to local database
                val entities = sermons.map { sermon ->
                    SermonEntity(
                        id = sermon.id,
                        title = sermon.title,
                        speaker = sermon.speaker,
                        date = sermon.date,
                        ministry = sermon.ministry,
                        description = sermon.description,
                        thumbnail = sermon.thumbnail,
                        fileUrl = sermon.fileUrl
                    )
                }
                database.sermonDao().insertSermons(entities)
                emit(Result.success(response.body()!!))
            } else {
                emit(Result.failure(Exception("Failed to fetch sermons")))
            }
        } catch (e: Exception) {
            // Try to get from cache
            try {
                val cachedSermons = database.sermonDao().getAllSermons()
                // Convert to response
                emit(Result.failure(e))
            } catch (cacheError: Exception) {
                emit(Result.failure(e))
            }
        }
    }

    fun getSermonsByMinistry(ministry: String): Flow<Result<SermonsResponse>> = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.getSermonsByMinistry(
                ministry = ministry,
                csrfToken = csrfToken
            )

            if (response.isSuccessful && response.body() != null) {
                val sermons = response.body()!!.sermons
                val entities = sermons.map { sermon ->
                    SermonEntity(
                        id = sermon.id,
                        title = sermon.title,
                        speaker = sermon.speaker,
                        date = sermon.date,
                        ministry = sermon.ministry,
                        description = sermon.description,
                        thumbnail = sermon.thumbnail,
                        fileUrl = sermon.fileUrl
                    )
                }
                database.sermonDao().insertSermons(entities)
                emit(Result.success(response.body()!!))
            } else {
                emit(Result.failure(Exception("Failed to fetch sermons")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun uploadSermon(
        title: String,
        speaker: String,
        ministry: String,
        date: String,
        description: String,
        filePath: String,
        thumbnailPath: String? = null
    ): Flow<Result<String>> = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()

            val actionBody = "upload_sermon".toRequestBody("text/plain".toMediaType())
            val titleBody = title.toRequestBody("text/plain".toMediaType())
            val speakerBody = speaker.toRequestBody("text/plain".toMediaType())
            val ministryBody = ministry.toRequestBody("text/plain".toMediaType())
            val dateBody = date.toRequestBody("text/plain".toMediaType())
            val descriptionBody = description.toRequestBody("text/plain".toMediaType())
            val csrfBody = csrfToken.toRequestBody("text/plain".toMediaType())

            val file = File(filePath)
            val fileRequestBody = file.asRequestBody("video/mp4".toMediaType())
            val filePart = MultipartBody.Part.createFormData("file", file.name, fileRequestBody)

            var thumbnailPart: MultipartBody.Part? = null
            if (thumbnailPath != null) {
                val thumbnailFile = File(thumbnailPath)
                val thumbnailRequestBody = thumbnailFile.asRequestBody("image/jpeg".toMediaType())
                thumbnailPart = MultipartBody.Part.createFormData("thumbnail", thumbnailFile.name, thumbnailRequestBody)
            }

            val response = apiService.uploadSermon(
                action = actionBody,
                title = titleBody,
                speaker = speakerBody,
                ministry = ministryBody,
                date = dateBody,
                description = descriptionBody,
                csrfToken = csrfBody,
                file = filePart,
                thumbnail = thumbnailPart
            )

            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success(response.body()?.message ?: "Sermon uploaded successfully"))
            } else {
                emit(Result.failure(Exception(response.body()?.message ?: "Upload failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun deleteSermon(id: Int): Flow<Result<String>> = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.deleteSermon(
                id = id,
                csrfToken = csrfToken
            )

            if (response.isSuccessful && response.body()?.success == true) {
                database.sermonDao().deleteSermonById(id)
                emit(Result.success("Sermon deleted successfully"))
            } else {
                emit(Result.failure(Exception(response.body()?.message ?: "Delete failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun updateSermon(
        id: Int,
        title: String,
        speaker: String,
        description: String
    ): Flow<Result<String>> = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.updateSermon(
                id = id,
                title = title,
                speaker = speaker,
                description = description,
                csrfToken = csrfToken
            )

            if (response.isSuccessful && response.body()?.success == true) {
                emit(Result.success("Sermon updated successfully"))
            } else {
                emit(Result.failure(Exception(response.body()?.message ?: "Update failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun toggleFavorite(id: Int, isFavorite: Boolean) = flow {
        database.sermonDao().toggleFavorite(id, isFavorite)
    }

    fun getFavoriteSermons() = database.sermonDao().getFavoriteSermons()

    fun getDownloadedSermons() = database.sermonDao().getDownloadedSermons()

    fun searchSermons(query: String) = database.sermonDao().searchSermons(query)
}

package com.cefc.volunteers.util

import android.content.Context
import android.database.Cursor
import android.net.Uri
import android.provider.MediaStore
import android.provider.OpenableColumns
import java.io.File
import java.io.FileOutputStream

object FileUtils {

    fun getPathFromUri(context: Context, uri: Uri): String? {
        return when {
            uri.scheme == "content" -> {
                val cursor: Cursor? = context.contentResolver.query(uri, null, null, null, null)
                cursor?.use {
                    it.moveToFirst()
                    val displayName = it.getString(it.getColumnIndex(OpenableColumns.DISPLAY_NAME))
                    val inputStream = context.contentResolver.openInputStream(uri)
                    val file = File(context.cacheDir, displayName)
                    inputStream?.use { input ->
                        FileOutputStream(file).use { output ->
                            input.copyTo(output)
                        }
                    }
                    file.absolutePath
                }
            }
            uri.scheme == "file" -> uri.path
            else -> null
        }
    }

    fun getFileName(context: Context, uri: Uri): String {
        var name = "file"
        val cursor = context.contentResolver.query(uri, null, null, null, null)
        cursor?.use {
            it.moveToFirst()
            val displayNameIndex = it.getColumnIndex(OpenableColumns.DISPLAY_NAME)
            if (displayNameIndex >= 0) {
                name = it.getString(displayNameIndex)
            }
        }
        return name
    }

    fun getFileSize(context: Context, uri: Uri): Long {
        var size = 0L
        val cursor = context.contentResolver.query(uri, null, null, null, null)
        cursor?.use {
            it.moveToFirst()
            val sizeIndex = it.getColumnIndex(OpenableColumns.SIZE)
            if (sizeIndex >= 0) {
                size = it.getLong(sizeIndex)
            }
        }
        return size
    }

    fun formatFileSize(bytes: Long): String {
        return when {
            bytes <= 0 -> "0 B"
            bytes < 1024 -> "$bytes B"
            bytes < 1024 * 1024 -> "${bytes / 1024} KB"
            bytes < 1024 * 1024 * 1024 -> "${bytes / (1024 * 1024)} MB"
            else -> "${bytes / (1024 * 1024 * 1024)} GB"
        }
    }

    fun deleteFile(path: String): Boolean {
        return try {
            File(path).delete()
        } catch (e: Exception) {
            false
        }
    }

    fun isValidVideoFile(path: String): Boolean {
        val validExtensions = listOf("mp4", "mkv", "avi", "mov", "flv", "wmv")
        val extension = path.substringAfterLast(".").lowercase()
        return validExtensions.contains(extension)
    }

    fun isValidImageFile(path: String): Boolean {
        val validExtensions = listOf("jpg", "jpeg", "png", "gif", "webp")
        val extension = path.substringAfterLast(".").lowercase()
        return validExtensions.contains(extension)
    }
}

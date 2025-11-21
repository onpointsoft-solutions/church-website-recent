package com.cefc.volunteers.util

import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey
import java.util.*

class SecurityManager(context: Context) {

    private val masterKey = MasterKey.Builder(context)
        .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
        .build()

    private val encryptedPreferences = EncryptedSharedPreferences.create(
        context,
        "secret_shared_prefs",
        masterKey,
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
    )

    fun saveAuthToken(email: String, password: String) {
        encryptedPreferences.edit().apply {
            putString("auth_email", email)
            putString("auth_password", password)
            putLong("token_timestamp", System.currentTimeMillis())
            apply()
        }
    }

    fun getAuthToken(): Pair<String, String>? {
        val email = encryptedPreferences.getString("auth_email", null)
        val password = encryptedPreferences.getString("auth_password", null)
        return if (email != null && password != null) Pair(email, password) else null
    }

    fun clearAuthToken() {
        encryptedPreferences.edit().apply {
            remove("auth_email")
            remove("auth_password")
            remove("token_timestamp")
            apply()
        }
    }

    fun getCsrfToken(): String {
        // Generate a simple CSRF token for now
        // In production, this should be fetched from the server
        return UUID.randomUUID().toString()
    }

    fun saveCsrfToken(token: String) {
        encryptedPreferences.edit().apply {
            putString("csrf_token", token)
            apply()
        }
    }

    fun isTokenExpired(): Boolean {
        val timestamp = encryptedPreferences.getLong("token_timestamp", 0)
        val currentTime = System.currentTimeMillis()
        val oneHour = 60 * 60 * 1000
        return currentTime - timestamp > oneHour
    }
}

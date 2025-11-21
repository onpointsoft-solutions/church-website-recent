package com.cefc.volunteers.util

import android.content.Context
import android.content.SharedPreferences

class PreferenceManager(context: Context) {

    private val preferences: SharedPreferences = context.getSharedPreferences(
        "cefc_volunteers_prefs",
        Context.MODE_PRIVATE
    )

    fun setLoggedIn(isLoggedIn: Boolean) {
        preferences.edit().putBoolean("is_logged_in", isLoggedIn).apply()
    }

    fun isLoggedIn(): Boolean {
        return preferences.getBoolean("is_logged_in", false)
    }

    fun setUserEmail(email: String) {
        preferences.edit().putString("user_email", email).apply()
    }

    fun getUserEmail(): String {
        return preferences.getString("user_email", "") ?: ""
    }

    fun setUserName(name: String) {
        preferences.edit().putString("user_name", name).apply()
    }

    fun getUserName(): String {
        return preferences.getString("user_name", "User") ?: "User"
    }

    fun setUserMinistry(ministry: String) {
        preferences.edit().putString("user_ministry", ministry).apply()
    }

    fun getUserMinistry(): String {
        return preferences.getString("user_ministry", "General") ?: "General"
    }

    fun setUserId(id: Int) {
        preferences.edit().putInt("user_id", id).apply()
    }

    fun getUserId(): Int {
        return preferences.getInt("user_id", 0)
    }

    fun setLastSyncTime(time: Long) {
        preferences.edit().putLong("last_sync_time", time).apply()
    }

    fun getLastSyncTime(): Long {
        return preferences.getLong("last_sync_time", 0)
    }

    fun setDownloadPath(path: String) {
        preferences.edit().putString("download_path", path).apply()
    }

    fun getDownloadPath(): String {
        return preferences.getString("download_path", "") ?: ""
    }

    fun clearAll() {
        preferences.edit().clear().apply()
    }
}

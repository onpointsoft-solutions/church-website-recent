package com.cefc.volunteers.ui.activity

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.appcompat.app.AppCompatActivity
import com.cefc.volunteers.R
import com.cefc.volunteers.util.PreferenceManager

class SplashActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)

        val preferenceManager = PreferenceManager(this)

        Handler(Looper.getMainLooper()).postDelayed({
            val isLoggedIn = preferenceManager.isLoggedIn()
            val intent = if (isLoggedIn) {
                Intent(this, MainActivity::class.java)
            } else {
                Intent(this, LoginActivity::class.java)
            }
            startActivity(intent)
            finish()
        }, 2000) // 2 second splash screen
    }
}

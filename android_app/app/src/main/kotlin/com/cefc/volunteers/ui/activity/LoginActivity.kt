package com.cefc.volunteers.ui.activity

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.cefc.volunteers.R
import com.cefc.volunteers.data.api.RetrofitClient
import com.cefc.volunteers.data.local.AppDatabase
import com.cefc.volunteers.data.repository.AuthRepository
import com.cefc.volunteers.databinding.ActivityLoginBinding
import com.cefc.volunteers.util.PreferenceManager
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private lateinit var authRepository: AuthRepository
    private lateinit var preferenceManager: PreferenceManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        preferenceManager = PreferenceManager(this)
        val database = AppDatabase.getDatabase(this)
        val securityManager = SecurityManager(this)
        authRepository = AuthRepository(
            RetrofitClient.getApiService(),
            database,
            securityManager
        )

        setupUI()
    }

    private fun setupUI() {
        binding.loginButton.setOnClickListener {
            val email = binding.emailInput.text.toString().trim()
            val password = binding.passwordInput.text.toString()

            if (validateInputs(email, password)) {
                performLogin(email, password)
            }
        }

        binding.signupLink.setOnClickListener {
            startActivity(Intent(this, SignupActivity::class.java))
        }
    }

    private fun validateInputs(email: String, password: String): Boolean {
        return when {
            email.isEmpty() -> {
                binding.emailInput.error = "Email is required"
                false
            }
            !android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches() -> {
                binding.emailInput.error = "Invalid email format"
                false
            }
            password.isEmpty() -> {
                binding.passwordInput.error = "Password is required"
                false
            }
            password.length < 6 -> {
                binding.passwordInput.error = "Password must be at least 6 characters"
                false
            }
            else -> true
        }
    }

    private fun performLogin(email: String, password: String) {
        binding.loginButton.isEnabled = false
        binding.progressBar.visibility = android.view.View.VISIBLE

        lifecycleScope.launch {
            authRepository.login(email, password).collect { result ->
                result.onSuccess { response ->
                    if (response.success) {
                        preferenceManager.setLoggedIn(true)
                        preferenceManager.setUserEmail(email)
                        response.user?.let {
                            preferenceManager.setUserName(it.name)
                            preferenceManager.setUserMinistry(it.ministry)
                        }
                        startActivity(Intent(this@LoginActivity, MainActivity::class.java))
                        finish()
                    } else {
                        showError(response.message)
                    }
                }
                result.onFailure { error ->
                    showError(error.message ?: "Login failed")
                }
                binding.loginButton.isEnabled = true
                binding.progressBar.visibility = android.view.View.GONE
            }
        }
    }

    private fun showError(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
    }
}

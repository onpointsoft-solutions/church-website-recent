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
import com.cefc.volunteers.databinding.ActivitySignupBinding
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.launch

class SignupActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySignupBinding
    private lateinit var authRepository: AuthRepository

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySignupBinding.inflate(layoutInflater)
        setContentView(binding.root)

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
        binding.signupButton.setOnClickListener {
            val name = binding.nameInput.text.toString().trim()
            val email = binding.emailInput.text.toString().trim()
            val phone = binding.phoneInput.text.toString().trim()
            val ministry = binding.ministryInput.text.toString().trim()
            val password = binding.passwordInput.text.toString()
            val confirmPassword = binding.confirmPasswordInput.text.toString()

            if (validateInputs(name, email, phone, ministry, password, confirmPassword)) {
                performSignup(name, email, phone, ministry, password)
            }
        }

        binding.loginLink.setOnClickListener {
            finish()
        }
    }

    private fun validateInputs(
        name: String,
        email: String,
        phone: String,
        ministry: String,
        password: String,
        confirmPassword: String
    ): Boolean {
        return when {
            name.isEmpty() -> {
                binding.nameInput.error = "Name is required"
                false
            }
            email.isEmpty() -> {
                binding.emailInput.error = "Email is required"
                false
            }
            !android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches() -> {
                binding.emailInput.error = "Invalid email format"
                false
            }
            phone.isEmpty() -> {
                binding.phoneInput.error = "Phone is required"
                false
            }
            ministry.isEmpty() -> {
                binding.ministryInput.error = "Ministry is required"
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
            password != confirmPassword -> {
                binding.confirmPasswordInput.error = "Passwords do not match"
                false
            }
            else -> true
        }
    }

    private fun performSignup(
        name: String,
        email: String,
        phone: String,
        ministry: String,
        password: String
    ) {
        binding.signupButton.isEnabled = false
        binding.progressBar.visibility = android.view.View.VISIBLE

        lifecycleScope.launch {
            authRepository.signup(name, email, phone, ministry, password).collect { result ->
                result.onSuccess { response ->
                    if (response.success) {
                        showSuccess("Registration successful! Please wait for admin verification.")
                        finish()
                    } else {
                        showError(response.message)
                    }
                }
                result.onFailure { error ->
                    showError(error.message ?: "Signup failed")
                }
                binding.signupButton.isEnabled = true
                binding.progressBar.visibility = android.view.View.GONE
            }
        }
    }

    private fun showError(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
    }

    private fun showSuccess(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_LONG).show()
    }
}

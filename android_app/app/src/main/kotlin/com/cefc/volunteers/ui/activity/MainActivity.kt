package com.cefc.volunteers.ui.activity

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.cefc.volunteers.R
import com.cefc.volunteers.data.api.RetrofitClient
import com.cefc.volunteers.data.local.AppDatabase
import com.cefc.volunteers.data.repository.AuthRepository
import com.cefc.volunteers.data.repository.SermonRepository
import com.cefc.volunteers.databinding.ActivityMainBinding
import com.cefc.volunteers.ui.adapter.SermonAdapter
import com.cefc.volunteers.util.PreferenceManager
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var sermonRepository: SermonRepository
    private lateinit var authRepository: AuthRepository
    private lateinit var preferenceManager: PreferenceManager
    private lateinit var sermonAdapter: SermonAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        preferenceManager = PreferenceManager(this)
        val database = AppDatabase.getDatabase(this)
        val securityManager = SecurityManager(this)
        val apiService = RetrofitClient.getApiService()

        sermonRepository = SermonRepository(apiService, database, securityManager)
        authRepository = AuthRepository(apiService, database, securityManager)

        setupUI()
        loadSermons()
    }

    private fun setupUI() {
        sermonAdapter = SermonAdapter { sermon ->
            val intent = Intent(this, SermonDetailActivity::class.java)
            intent.putExtra("sermon", sermon)
            startActivity(intent)
        }

        binding.sermonsRecyclerView.apply {
            layoutManager = LinearLayoutManager(this@MainActivity)
            adapter = sermonAdapter
        }

        binding.uploadButton.setOnClickListener {
            startActivity(Intent(this, UploadSermonActivity::class.java))
        }

        binding.profileButton.setOnClickListener {
            showProfileMenu()
        }

        binding.swipeRefresh.setOnRefreshListener {
            loadSermons()
        }

        // Set user greeting
        val userName = preferenceManager.getUserName()
        binding.greetingText.text = "Welcome, $userName!"
    }

    private fun loadSermons() {
        binding.progressBar.visibility = android.view.View.VISIBLE

        lifecycleScope.launch {
            sermonRepository.getSermons().collect { result ->
                result.onSuccess { response ->
                    sermonAdapter.submitList(response.sermons)
                }
                result.onFailure { error ->
                    showError(error.message ?: "Failed to load sermons")
                }
                binding.progressBar.visibility = android.view.View.GONE
                binding.swipeRefresh.isRefreshing = false
            }
        }
    }

    private fun showProfileMenu() {
        val options = arrayOf("Profile", "Favorites", "Downloads", "Logout")
        androidx.appcompat.app.AlertDialog.Builder(this)
            .setTitle("Menu")
            .setItems(options) { _, which ->
                when (which) {
                    0 -> showProfile()
                    1 -> showFavorites()
                    2 -> showDownloads()
                    3 -> performLogout()
                }
            }
            .show()
    }

    private fun showProfile() {
        val userName = preferenceManager.getUserName()
        val userEmail = preferenceManager.getUserEmail()
        val ministry = preferenceManager.getUserMinistry()

        val message = "Name: $userName\nEmail: $userEmail\nMinistry: $ministry"
        androidx.appcompat.app.AlertDialog.Builder(this)
            .setTitle("Profile")
            .setMessage(message)
            .setPositiveButton("OK", null)
            .show()
    }

    private fun showFavorites() {
        lifecycleScope.launch {
            sermonRepository.getFavoriteSermons().collect { favorites ->
                sermonAdapter.submitList(favorites.map { entity ->
                    com.cefc.volunteers.data.model.Sermon(
                        id = entity.id,
                        title = entity.title,
                        speaker = entity.speaker,
                        date = entity.date,
                        ministry = entity.ministry,
                        description = entity.description,
                        thumbnail = entity.thumbnail,
                        fileUrl = entity.fileUrl
                    )
                })
            }
        }
    }

    private fun showDownloads() {
        lifecycleScope.launch {
            sermonRepository.getDownloadedSermons().collect { downloads ->
                sermonAdapter.submitList(downloads.map { entity ->
                    com.cefc.volunteers.data.model.Sermon(
                        id = entity.id,
                        title = entity.title,
                        speaker = entity.speaker,
                        date = entity.date,
                        ministry = entity.ministry,
                        description = entity.description,
                        thumbnail = entity.thumbnail,
                        fileUrl = entity.fileUrl
                    )
                })
            }
        }
    }

    private fun performLogout() {
        lifecycleScope.launch {
            authRepository.logout().collect { result ->
                result.onSuccess {
                    preferenceManager.setLoggedIn(false)
                    startActivity(Intent(this@MainActivity, LoginActivity::class.java))
                    finish()
                }
                result.onFailure { error ->
                    showError(error.message ?: "Logout failed")
                }
            }
        }
    }

    private fun showError(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
    }
}

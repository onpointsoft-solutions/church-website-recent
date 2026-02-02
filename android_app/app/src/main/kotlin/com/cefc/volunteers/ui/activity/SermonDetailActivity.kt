package com.cefc.volunteers.ui.activity

import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.bumptech.glide.Glide
import com.cefc.volunteers.R
import com.cefc.volunteers.data.api.RetrofitClient
import com.cefc.volunteers.data.local.AppDatabase
import com.cefc.volunteers.data.model.Sermon
import com.cefc.volunteers.data.repository.SermonRepository
import com.cefc.volunteers.databinding.ActivitySermonDetailBinding
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.launch

class SermonDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivitySermonDetailBinding
    private lateinit var sermonRepository: SermonRepository
    private var sermon: Sermon? = null
    private var isFavorite = false

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySermonDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val database = AppDatabase.getDatabase(this)
        val securityManager = SecurityManager(this)
        sermonRepository = SermonRepository(
            RetrofitClient.getApiService(),
            database,
            securityManager
        )

        sermon = intent.getSerializableExtra("sermon") as? Sermon
        sermon?.let { displaySermon(it) }

        setupUI()
    }

    private fun displaySermon(sermon: Sermon) {
        binding.titleText.text = sermon.title
        binding.speakerText.text = "Speaker: ${sermon.speaker}"
        binding.dateText.text = "Date: ${sermon.date}"
        binding.ministryText.text = "Ministry: ${sermon.ministry ?: "General"}"
        binding.descriptionText.text = sermon.description ?: "No description available"

        // Load thumbnail
        sermon.thumbnail?.let {
            Glide.with(this)
                .load(it)
                .placeholder(R.drawable.ic_placeholder)
                .into(binding.thumbnailImage)
        }
    }

    private fun setupUI() {
        binding.favoriteButton.setOnClickListener {
            toggleFavorite()
        }

        binding.downloadButton.setOnClickListener {
            downloadSermon()
        }

        binding.shareButton.setOnClickListener {
            shareSermon()
        }

        binding.backButton.setOnClickListener {
            finish()
        }
    }

    private fun toggleFavorite() {
        sermon?.let { s ->
            isFavorite = !isFavorite
            lifecycleScope.launch {
                sermonRepository.toggleFavorite(s.id, isFavorite).collect {
                    val message = if (isFavorite) "Added to favorites" else "Removed from favorites"
                    Toast.makeText(this@SermonDetailActivity, message, Toast.LENGTH_SHORT).show()
                    updateFavoriteButton()
                }
            }
        }
    }

    private fun updateFavoriteButton() {
        binding.favoriteButton.setImageResource(
            if (isFavorite) R.drawable.ic_favorite_filled else R.drawable.ic_favorite_outline
        )
    }

    private fun downloadSermon() {
        sermon?.let { s ->
            Toast.makeText(this, "Download feature coming soon", Toast.LENGTH_SHORT).show()
            // Implement download functionality
        }
    }

    private fun shareSermon() {
        sermon?.let { s ->
            val shareText = "Check out this sermon: ${s.title} by ${s.speaker}"
            val intent = android.content.Intent().apply {
                action = android.content.Intent.ACTION_SEND
                putExtra(android.content.Intent.EXTRA_TEXT, shareText)
                type = "text/plain"
            }
            startActivity(android.content.Intent.createChooser(intent, "Share Sermon"))
        }
    }
}

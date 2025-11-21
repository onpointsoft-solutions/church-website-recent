package com.cefc.volunteers.ui.activity

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.cefc.volunteers.R
import com.cefc.volunteers.data.api.RetrofitClient
import com.cefc.volunteers.data.local.AppDatabase
import com.cefc.volunteers.data.repository.SermonRepository
import com.cefc.volunteers.databinding.ActivityUploadSermonBinding
import com.cefc.volunteers.util.FileUtils
import com.cefc.volunteers.util.PreferenceManager
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*

class UploadSermonActivity : AppCompatActivity() {

    private lateinit var binding: ActivityUploadSermonBinding
    private lateinit var sermonRepository: SermonRepository
    private lateinit var preferenceManager: PreferenceManager
    private var selectedFilePath: String? = null
    private var selectedThumbnailPath: String? = null

    companion object {
        private const val REQUEST_VIDEO = 1
        private const val REQUEST_THUMBNAIL = 2
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityUploadSermonBinding.inflate(layoutInflater)
        setContentView(binding.root)

        preferenceManager = PreferenceManager(this)
        val database = AppDatabase.getDatabase(this)
        val securityManager = SecurityManager(this)
        sermonRepository = SermonRepository(
            RetrofitClient.getApiService(),
            database,
            securityManager
        )

        setupUI()
    }

    private fun setupUI() {
        binding.selectVideoButton.setOnClickListener {
            selectVideo()
        }

        binding.selectThumbnailButton.setOnClickListener {
            selectThumbnail()
        }

        binding.uploadButton.setOnClickListener {
            validateAndUpload()
        }

        binding.cancelButton.setOnClickListener {
            finish()
        }

        // Set default date to today
        val dateFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        binding.dateInput.setText(dateFormat.format(Date()))

        // Set ministry from preference
        binding.ministryInput.setText(preferenceManager.getUserMinistry())
    }

    private fun selectVideo() {
        val intent = Intent(Intent.ACTION_GET_CONTENT).apply {
            type = "video/*"
        }
        startActivityForResult(intent, REQUEST_VIDEO)
    }

    private fun selectThumbnail() {
        val intent = Intent(Intent.ACTION_GET_CONTENT).apply {
            type = "image/*"
        }
        startActivityForResult(intent, REQUEST_THUMBNAIL)
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)

        if (resultCode == RESULT_OK && data != null) {
            when (requestCode) {
                REQUEST_VIDEO -> {
                    data.data?.let { uri ->
                        selectedFilePath = FileUtils.getPathFromUri(this, uri)
                        binding.videoStatusText.text = "Video selected: ${FileUtils.getFileName(this, uri)}"
                    }
                }
                REQUEST_THUMBNAIL -> {
                    data.data?.let { uri ->
                        selectedThumbnailPath = FileUtils.getPathFromUri(this, uri)
                        binding.thumbnailStatusText.text = "Thumbnail selected: ${FileUtils.getFileName(this, uri)}"
                    }
                }
            }
        }
    }

    private fun validateAndUpload() {
        val title = binding.titleInput.text.toString().trim()
        val speaker = binding.speakerInput.text.toString().trim()
        val ministry = binding.ministryInput.text.toString().trim()
        val date = binding.dateInput.text.toString().trim()
        val description = binding.descriptionInput.text.toString().trim()

        when {
            title.isEmpty() -> {
                binding.titleInput.error = "Title is required"
            }
            speaker.isEmpty() -> {
                binding.speakerInput.error = "Speaker is required"
            }
            ministry.isEmpty() -> {
                binding.ministryInput.error = "Ministry is required"
            }
            date.isEmpty() -> {
                binding.dateInput.error = "Date is required"
            }
            description.isEmpty() -> {
                binding.descriptionInput.error = "Description is required"
            }
            selectedFilePath == null -> {
                showError("Please select a video file")
            }
            else -> {
                performUpload(title, speaker, ministry, date, description)
            }
        }
    }

    private fun performUpload(
        title: String,
        speaker: String,
        ministry: String,
        date: String,
        description: String
    ) {
        binding.uploadButton.isEnabled = false
        binding.progressBar.visibility = android.view.View.VISIBLE
        binding.progressText.text = "Uploading..."

        lifecycleScope.launch {
            sermonRepository.uploadSermon(
                title = title,
                speaker = speaker,
                ministry = ministry,
                date = date,
                description = description,
                filePath = selectedFilePath!!,
                thumbnailPath = selectedThumbnailPath
            ).collect { result ->
                result.onSuccess { message ->
                    showSuccess(message)
                    finish()
                }
                result.onFailure { error ->
                    showError(error.message ?: "Upload failed")
                }
                binding.uploadButton.isEnabled = true
                binding.progressBar.visibility = android.view.View.GONE
                binding.progressText.text = ""
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

package com.cefc.volunteers.ui.activity

import android.Manifest
import android.app.Activity
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Bundle
import android.provider.MediaStore
import android.view.View
import android.widget.*
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.GridLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.cefc.volunteers.R
import com.cefc.volunteers.data.api.RetrofitClient
import com.cefc.volunteers.data.model.GalleryImage
import com.cefc.volunteers.data.model.GalleryResponse
import com.cefc.volunteers.ui.adapter.GalleryAdapter
import com.cefc.volunteers.util.PreferenceManager
import com.google.android.material.floatingactionbutton.FloatingActionButton
import kotlinx.coroutines.*
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File

class GalleryActivity : AppCompatActivity() {
    
    private lateinit var recyclerView: RecyclerView
    private lateinit var progressBar: ProgressBar
    private lateinit var emptyView: TextView
    private lateinit var fabUpload: FloatingActionButton
    private lateinit var categorySpinner: Spinner
    
    private lateinit var galleryAdapter: GalleryAdapter
    private lateinit var preferenceManager: PreferenceManager
    
    private var selectedImageUri: Uri? = null
    private val scope = CoroutineScope(Dispatchers.Main + Job())
    
    private val pickImageLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            selectedImageUri = result.data?.data
            showUploadDialog()
        }
    }
    
    private val requestPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestPermission()
    ) { isGranted ->
        if (isGranted) {
            openImagePicker()
        } else {
            Toast.makeText(this, "Permission denied", Toast.LENGTH_SHORT).show()
        }
    }
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_gallery)
        
        preferenceManager = PreferenceManager(this)
        
        initViews()
        setupRecyclerView()
        setupCategoryFilter()
        loadGalleryImages()
        
        // Show upload button only for admins
        if (preferenceManager.isAdmin()) {
            fabUpload.visibility = View.VISIBLE
            fabUpload.setOnClickListener {
                checkPermissionAndPickImage()
            }
        }
    }
    
    private fun initViews() {
        recyclerView = findViewById(R.id.recyclerViewGallery)
        progressBar = findViewById(R.id.progressBar)
        emptyView = findViewById(R.id.emptyView)
        fabUpload = findViewById(R.id.fabUpload)
        categorySpinner = findViewById(R.id.categorySpinner)
        
        supportActionBar?.setDisplayHomeAsUpEnabled(true)
        supportActionBar?.title = "Gallery"
    }
    
    private fun setupRecyclerView() {
        galleryAdapter = GalleryAdapter(
            onImageClick = { image ->
                showImageDetail(image)
            },
            onDeleteClick = { image ->
                if (preferenceManager.isAdmin()) {
                    confirmDelete(image)
                }
            },
            isAdmin = preferenceManager.isAdmin()
        )
        
        recyclerView.apply {
            layoutManager = GridLayoutManager(this@GalleryActivity, 2)
            adapter = galleryAdapter
        }
    }
    
    private fun setupCategoryFilter() {
        val categories = listOf("All", "Worship", "Events", "Ministry", "Community", "General")
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, categories)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        categorySpinner.adapter = adapter
        
        categorySpinner.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(parent: AdapterView<*>?, view: View?, position: Int, id: Long) {
                val category = if (position == 0) null else categories[position].lowercase()
                loadGalleryImages(category)
            }
            
            override fun onNothingSelected(parent: AdapterView<*>?) {}
        }
    }
    
    private fun loadGalleryImages(category: String? = null) {
        progressBar.visibility = View.VISIBLE
        emptyView.visibility = View.GONE
        
        scope.launch {
            try {
                val response = withContext(Dispatchers.IO) {
                    if (category != null) {
                        RetrofitClient.apiService.getGalleryImages(category = category)
                    } else {
                        RetrofitClient.apiService.getGalleryImages()
                    }
                }
                
                progressBar.visibility = View.GONE
                
                if (response.isSuccessful && response.body()?.success == true) {
                    val images = response.body()?.images ?: emptyList()
                    if (images.isEmpty()) {
                        emptyView.visibility = View.VISIBLE
                        emptyView.text = "No images in gallery yet"
                    } else {
                        galleryAdapter.submitList(images)
                    }
                } else {
                    Toast.makeText(
                        this@GalleryActivity,
                        "Failed to load gallery",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            } catch (e: Exception) {
                progressBar.visibility = View.GONE
                emptyView.visibility = View.VISIBLE
                emptyView.text = "Error loading gallery"
                Toast.makeText(
                    this@GalleryActivity,
                    "Error: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }
    
    private fun checkPermissionAndPickImage() {
        when {
            ContextCompat.checkSelfPermission(
                this,
                Manifest.permission.READ_EXTERNAL_STORAGE
            ) == PackageManager.PERMISSION_GRANTED -> {
                openImagePicker()
            }
            else -> {
                requestPermissionLauncher.launch(Manifest.permission.READ_EXTERNAL_STORAGE)
            }
        }
    }
    
    private fun openImagePicker() {
        val intent = Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI)
        pickImageLauncher.launch(intent)
    }
    
    private fun showUploadDialog() {
        val dialogView = layoutInflater.inflate(R.layout.dialog_upload_image, null)
        val etTitle = dialogView.findViewById<EditText>(R.id.etTitle)
        val etDescription = dialogView.findViewById<EditText>(R.id.etDescription)
        val spinnerCategory = dialogView.findViewById<Spinner>(R.id.spinnerCategory)
        val cbFeatured = dialogView.findViewById<CheckBox>(R.id.cbFeatured)
        val ivPreview = dialogView.findViewById<ImageView>(R.id.ivPreview)
        
        // Show preview
        ivPreview.setImageURI(selectedImageUri)
        
        // Setup category spinner
        val categories = listOf("Worship", "Events", "Ministry", "Community", "General")
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_item, categories)
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item)
        spinnerCategory.adapter = adapter
        
        AlertDialog.Builder(this)
            .setTitle("Upload Image")
            .setView(dialogView)
            .setPositiveButton("Upload") { _, _ ->
                val title = etTitle.text.toString().trim()
                val description = etDescription.text.toString().trim()
                val category = spinnerCategory.selectedItem.toString().lowercase()
                val isFeatured = cbFeatured.isChecked
                
                if (title.isNotEmpty()) {
                    uploadImage(title, description, category, isFeatured)
                } else {
                    Toast.makeText(this, "Title is required", Toast.LENGTH_SHORT).show()
                }
            }
            .setNegativeButton("Cancel", null)
            .show()
    }
    
    private fun uploadImage(title: String, description: String, category: String, isFeatured: Boolean) {
        selectedImageUri?.let { uri ->
            progressBar.visibility = View.VISIBLE
            
            scope.launch {
                try {
                    val file = getFileFromUri(uri)
                    val requestFile = file.asRequestBody("image/*".toMediaTypeOrNull())
                    val imagePart = MultipartBody.Part.createFormData("image", file.name, requestFile)
                    
                    val actionBody = "upload".toRequestBody("text/plain".toMediaTypeOrNull())
                    val titleBody = title.toRequestBody("text/plain".toMediaTypeOrNull())
                    val descBody = description.toRequestBody("text/plain".toMediaTypeOrNull())
                    val categoryBody = category.toRequestBody("text/plain".toMediaTypeOrNull())
                    val featuredBody = isFeatured.toString().toRequestBody("text/plain".toMediaTypeOrNull())
                    val uploadedByBody = preferenceManager.getUserName().toRequestBody("text/plain".toMediaTypeOrNull())
                    val csrfBody = "token".toRequestBody("text/plain".toMediaTypeOrNull())
                    
                    val response = withContext(Dispatchers.IO) {
                        RetrofitClient.apiService.uploadGalleryImage(
                            action = actionBody,
                            title = titleBody,
                            description = descBody,
                            category = categoryBody,
                            isFeatured = featuredBody,
                            uploadedBy = uploadedByBody,
                            csrfToken = csrfBody,
                            image = imagePart
                        )
                    }
                    
                    progressBar.visibility = View.GONE
                    
                    if (response.isSuccessful && response.body()?.success == true) {
                        Toast.makeText(this@GalleryActivity, "Image uploaded successfully", Toast.LENGTH_SHORT).show()
                        loadGalleryImages()
                    } else {
                        Toast.makeText(
                            this@GalleryActivity,
                            response.body()?.message ?: "Upload failed",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                } catch (e: Exception) {
                    progressBar.visibility = View.GONE
                    Toast.makeText(
                        this@GalleryActivity,
                        "Error: ${e.message}",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            }
        }
    }
    
    private fun getFileFromUri(uri: Uri): File {
        val inputStream = contentResolver.openInputStream(uri)
        val file = File(cacheDir, "temp_image_${System.currentTimeMillis()}.jpg")
        file.outputStream().use { outputStream ->
            inputStream?.copyTo(outputStream)
        }
        return file
    }
    
    private fun showImageDetail(image: GalleryImage) {
        val dialogView = layoutInflater.inflate(R.layout.dialog_image_detail, null)
        val ivImage = dialogView.findViewById<ImageView>(R.id.ivImage)
        val tvTitle = dialogView.findViewById<TextView>(R.id.tvTitle)
        val tvDescription = dialogView.findViewById<TextView>(R.id.tvDescription)
        val tvCategory = dialogView.findViewById<TextView>(R.id.tvCategory)
        val tvViews = dialogView.findViewById<TextView>(R.id.tvViews)
        
        // Load image using Glide or similar
        // Glide.with(this).load(image.imageUrl).into(ivImage)
        
        tvTitle.text = image.title
        tvDescription.text = image.description
        tvCategory.text = "Category: ${image.category}"
        tvViews.text = "Views: ${image.views}"
        
        AlertDialog.Builder(this)
            .setView(dialogView)
            .setPositiveButton("Close", null)
            .show()
    }
    
    private fun confirmDelete(image: GalleryImage) {
        AlertDialog.Builder(this)
            .setTitle("Delete Image")
            .setMessage("Are you sure you want to delete this image?")
            .setPositiveButton("Delete") { _, _ ->
                deleteImage(image.id)
            }
            .setNegativeButton("Cancel", null)
            .show()
    }
    
    private fun deleteImage(imageId: Int) {
        progressBar.visibility = View.VISIBLE
        
        scope.launch {
            try {
                val response = withContext(Dispatchers.IO) {
                    RetrofitClient.apiService.deleteGalleryImage(
                        action = "delete",
                        id = imageId,
                        csrfToken = "token"
                    )
                }
                
                progressBar.visibility = View.GONE
                
                if (response.isSuccessful && response.body()?.success == true) {
                    Toast.makeText(this@GalleryActivity, "Image deleted", Toast.LENGTH_SHORT).show()
                    loadGalleryImages()
                } else {
                    Toast.makeText(
                        this@GalleryActivity,
                        "Failed to delete image",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            } catch (e: Exception) {
                progressBar.visibility = View.GONE
                Toast.makeText(
                    this@GalleryActivity,
                    "Error: ${e.message}",
                    Toast.LENGTH_SHORT
                ).show()
            }
        }
    }
    
    override fun onSupportNavigateUp(): Boolean {
        onBackPressed()
        return true
    }
    
    override fun onDestroy() {
        super.onDestroy()
        scope.cancel()
    }
}

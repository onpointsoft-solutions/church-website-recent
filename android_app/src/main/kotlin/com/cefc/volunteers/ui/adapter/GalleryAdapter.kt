package com.cefc.volunteers.ui.adapter

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageButton
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.cefc.volunteers.R
import com.cefc.volunteers.data.model.GalleryImage

class GalleryAdapter(
    private val onImageClick: (GalleryImage) -> Unit,
    private val onDeleteClick: (GalleryImage) -> Unit,
    private val isAdmin: Boolean
) : ListAdapter<GalleryImage, GalleryAdapter.GalleryViewHolder>(GalleryDiffCallback()) {
    
    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): GalleryViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_gallery, parent, false)
        return GalleryViewHolder(view)
    }
    
    override fun onBindViewHolder(holder: GalleryViewHolder, position: Int) {
        holder.bind(getItem(position))
    }
    
    inner class GalleryViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val ivThumbnail: ImageView = itemView.findViewById(R.id.ivThumbnail)
        private val tvTitle: TextView = itemView.findViewById(R.id.tvTitle)
        private val tvCategory: TextView = itemView.findViewById(R.id.tvCategory)
        private val btnDelete: ImageButton = itemView.findViewById(R.id.btnDelete)
        private val viewFeatured: View = itemView.findViewById(R.id.viewFeatured)
        
        fun bind(image: GalleryImage) {
            tvTitle.text = image.title
            tvCategory.text = image.category
            
            // Show featured indicator
            viewFeatured.visibility = if (image.isFeatured) View.VISIBLE else View.GONE
            
            // Show delete button only for admins
            btnDelete.visibility = if (isAdmin) View.VISIBLE else View.GONE
            
            // Load thumbnail using Glide or similar
            // Glide.with(itemView.context)
            //     .load(image.thumbnailUrl ?: image.imageUrl)
            //     .placeholder(R.drawable.placeholder_image)
            //     .into(ivThumbnail)
            
            itemView.setOnClickListener {
                onImageClick(image)
            }
            
            btnDelete.setOnClickListener {
                onDeleteClick(image)
            }
        }
    }
    
    class GalleryDiffCallback : DiffUtil.ItemCallback<GalleryImage>() {
        override fun areItemsTheSame(oldItem: GalleryImage, newItem: GalleryImage): Boolean {
            return oldItem.id == newItem.id
        }
        
        override fun areContentsTheSame(oldItem: GalleryImage, newItem: GalleryImage): Boolean {
            return oldItem == newItem
        }
    }
}

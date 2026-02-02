package com.cefc.volunteers.ui.adapter

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.cefc.volunteers.R
import com.cefc.volunteers.data.model.Sermon
import com.cefc.volunteers.databinding.ItemSermonBinding

class SermonAdapter(
    private val onItemClick: (Sermon) -> Unit
) : ListAdapter<Sermon, SermonAdapter.SermonViewHolder>(SermonDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): SermonViewHolder {
        val binding = ItemSermonBinding.inflate(
            LayoutInflater.from(parent.context),
            parent,
            false
        )
        return SermonViewHolder(binding, onItemClick)
    }

    override fun onBindViewHolder(holder: SermonViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class SermonViewHolder(
        private val binding: ItemSermonBinding,
        private val onItemClick: (Sermon) -> Unit
    ) : RecyclerView.ViewHolder(binding.root) {

        fun bind(sermon: Sermon) {
            binding.apply {
                titleText.text = sermon.title
                speakerText.text = sermon.speaker
                dateText.text = sermon.date
                ministryText.text = sermon.ministry ?: "General"

                // Load thumbnail
                sermon.thumbnail?.let {
                    Glide.with(itemView.context)
                        .load(it)
                        .placeholder(R.drawable.ic_placeholder)
                        .into(thumbnailImage)
                }

                root.setOnClickListener {
                    onItemClick(sermon)
                }
            }
        }
    }

    private class SermonDiffCallback : DiffUtil.ItemCallback<Sermon>() {
        override fun areItemsTheSame(oldItem: Sermon, newItem: Sermon): Boolean {
            return oldItem.id == newItem.id
        }

        override fun areContentsTheSame(oldItem: Sermon, newItem: Sermon): Boolean {
            return oldItem == newItem
        }
    }
}

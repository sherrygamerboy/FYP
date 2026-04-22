import React, { useState, useEffect } from 'react';
import DOMPurify from 'dompurify';

const BlogPost = ({ postId }) => {
  const [post, setPost] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Simulated API call
    fetch(`https://api.example.com/posts/${postId}`)
      .then((res) => res.json())
      .then((data) => {
        setPost(data);
        setLoading(false);
      });
  }, [postId]);

  if (loading) return <p>Loading post...</p>;

  /**
   * SECURITY LAYER: Sanitization
   * We scrub the HTML string before it ever touches the DOM.
   * This removes attributes like 'onerror' or 'onload' and tags like <script>.
   */
  const sanitizedContent = DOMPurify.sanitize(post.content, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'p', 'br', 'ul', 'ol', 'li', 'img'],
    ALLOWED_ATTR: ['src', 'alt', 'title', 'width', 'height']
  });

  return (
    <article className="blog-post">
      <h1>{post.title}</h1>
      <div 
        className="post-body"
        // CWE-79 Protection: Use the sanitized string, not the raw API response.
        dangerouslySetInnerHTML={{ __html: sanitizedContent }} 
      />
    </article>
  );
};

export default BlogPost;
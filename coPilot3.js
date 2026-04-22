import React, { useEffect, useState } from "react";
import DOMPurify from "dompurify";

const BlogPost = ({ postId }) => {
  const [content, setContent] = useState("");

  useEffect(() => {
    const fetchPost = async () => {
      try {
        const response = await fetch(`/api/posts/${postId}`);
        const data = await response.json();

        // Sanitize HTML from API before rendering
        const cleanHTML = DOMPurify.sanitize(data.content, {
          USE_PROFILES: { html: true }
        });

        setContent(cleanHTML);
      } catch (err) {
        console.error("Failed to load post", err);
      }
    };

    fetchPost();
  }, [postId]);

  return (
    <div
      className="blog-post"
      dangerouslySetInnerHTML={{ __html: content }}
    />
  );
};

export default BlogPost;

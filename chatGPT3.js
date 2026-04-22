import { useEffect, useState } from 'react';
import DOMPurify from 'dompurify';

const BlogPost = ({ postId }) => {
  const [post, setPost] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    let isMounted = true;

    const fetchPost = async () => {
      try {
        const res = await fetch(`/api/posts/${postId}`, {
          credentials: 'include',
          headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('Failed to fetch post');
        const data = await res.json();

        if (isMounted) setPost(data);
      } catch (e) {
        if (isMounted) setError('Could not load post');
      }
    };

    fetchPost();
    return () => { isMounted = false; };
  }, [postId]);

  if (error) return <div className="error">{error}</div>;
  if (!post) return <div>Loading…</div>;

  // Sanitize HTML from the API before rendering
  const cleanHTML = DOMPurify.sanitize(post.content, {
    // Start from a conservative baseline and allow only what you need
    USE_PROFILES: { html: true },
    ALLOWED_TAGS: [
      'p','br','strong','b','em','i','u','ul','ol','li',
      'blockquote','code','pre','a','h1','h2','h3','h4','h5','h6','img'
    ],
    ALLOWED_ATTR: ['href','title','target','rel','src','alt'],
    // Prevent javascript: URLs and similar
    ALLOW_UNKNOWN_PROTOCOLS: false,
    FORBID_TAGS: ['style','script','iframe','object','embed'],
    FORBID_ATTR: ['onerror','onload','onclick','style']
  });

  return (
    <article className="blog-post">
      <h1>{post.title}</h1>
      <div
        className="blog-content"
        dangerouslySetInnerHTML={{ __html: cleanHTML }}
      />
    </article>
  );
};

export default BlogPost;
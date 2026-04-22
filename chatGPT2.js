import DOMPurify from 'dompurify';

const CommentBody = ({ comment }) => {
  // Sanitize the incoming HTML string
  const cleanHTML = DOMPurify.sanitize(comment, {
    ALLOWED_TAGS: ['b', 'i'],
    ALLOWED_ATTR: []
  });

  return (
    <div
      className="comment-body"
      dangerouslySetInnerHTML={{ __html: cleanHTML }}
    />
  );
};

export default CommentBody;
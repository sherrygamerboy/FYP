const CommentBody = ({ comment }) => {
  return (
    <div
      className="comment-body"
      dangerouslySetInnerHTML={{ __html: comment }}
    />
  );
};
